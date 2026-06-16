<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Command\Email;

use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Entity\Template\Template;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\RequestOptions;
use League\CommonMark\Environment\Environment as CommonMarkEnvironment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Bulk-converts existing `format='html'` template rows to Notify-compatible `format='md'` rows
 * via the Claude API. Writes a Liquibase SQL patch containing one INSERT per converted row.
 * Validates each conversion by rendering it through Twig with the row's test data, then
 * round-tripping through league/commonmark with DisallowedRawHtmlExtension (matching the
 * runtime validator in UpdateTemplateSource). Failed rows are retried before failing the run.
 *
 *   ANTHROPIC_API_KEY=sk-... php bin/cli.php template:convert-to-md \
 *       --out=docs/vol-7238/VOL-7238-md-templates.sql
 */
class ConvertToMarkdownCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'template:convert-to-md';

    private const ANTHROPIC_API_URL = 'https://api.anthropic.com/v1/messages';
    private const ANTHROPIC_API_VERSION = '2023-06-01';
    private const DEFAULT_MODEL = 'claude-sonnet-4-6';
    private const DEFAULT_MAX_TOKENS = 8192;
    private const DEFAULT_RETRY_ATTEMPTS = 3;
    private const FORMAT_MARKDOWN = 'md';

    private ?MarkdownConverter $markdownValidator = null;
    private ?HttpClient $httpClient = null;

    public function __construct(
        CommandHandlerManager $commandHandlerManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly TwigRenderer $twigRenderer,
    ) {
        parent::__construct($commandHandlerManager);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Convert existing html template rows to md siblings via the Claude API; output Liquibase SQL.')
            ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Output SQL patch path', 'docs/vol-7238/VOL-7238-md-templates.sql')
            ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'Convert only this locale')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Convert only this template name')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'Claude model', self::DEFAULT_MODEL)
            ->addOption('retries', 'r', InputOption::VALUE_REQUIRED, 'Validation retry attempts per template', (string) self::DEFAULT_RETRY_ATTEMPTS)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Convert and validate but do not write the patch file')
            ->addOption('skip-default', null, InputOption::VALUE_NONE, 'Skip the layout-wrapper "default" template (recommended; Notify provides chrome)');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $apiKey = (string) getenv('ANTHROPIC_API_KEY');
        if ($apiKey === '') {
            $output->writeln('<error>ANTHROPIC_API_KEY environment variable is not set.</error>');
            return Command::FAILURE;
        }

        $outPath = (string) $input->getOption('out');
        $localeFilter = $input->getOption('locale');
        $nameFilter = $input->getOption('name');
        $model = (string) $input->getOption('model');
        $retries = max(1, (int) $input->getOption('retries'));
        $dryRun = (bool) $input->getOption('dry-run');
        $skipDefault = (bool) $input->getOption('skip-default') || $nameFilter === null;

        $candidates = $this->fetchCandidates($localeFilter, $nameFilter, $skipDefault);
        if ($candidates === []) {
            $output->writeln('<comment>No candidate rows to convert.</comment>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Converting %d row(s) using %s</info>', count($candidates), $model));

        $inserts = [];
        $failures = [];

        foreach ($candidates as $template) {
            $rowKey = sprintf('%s/%s', $template->getLocale(), $template->getName());
            $output->writeln(sprintf('  → %s', $rowKey));

            $result = $this->convertWithRetries($template, $apiKey, $model, $retries, $output);
            if ($result === null) {
                $failures[] = $rowKey;
                continue;
            }

            $inserts[] = $this->buildInsertSql($template, $result);
        }

        if (!$dryRun && $inserts !== []) {
            $this->writePatchFile($outPath, $inserts);
            $output->writeln(sprintf('<info>Wrote %d INSERT(s) to %s</info>', count($inserts), $outPath));
        } elseif ($dryRun) {
            $output->writeln(sprintf('<comment>Dry run: %d conversion(s) succeeded; patch file not written.</comment>', count($inserts)));
        }

        if ($failures !== []) {
            $output->writeln(sprintf('<error>%d row(s) failed conversion after %d attempt(s) each:</error>', count($failures), $retries));
            foreach ($failures as $f) {
                $output->writeln('  - ' . $f);
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Selects every `(locale, name)` pair that has a `format='html'` row but no `format='md'`
     * sibling yet. The html row is the conversion seed (richer content than plain).
     *
     * @return Template[]
     */
    private function fetchCandidates(?string $localeFilter, ?string $nameFilter, bool $skipDefault): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Template::class, 't')
            ->where('t.format = :html')
            ->setParameter('html', 'html')
            ->andWhere(
                'NOT EXISTS (
                    SELECT 1 FROM ' . Template::class . ' md
                    WHERE md.locale = t.locale AND md.name = t.name AND md.format = :md
                )'
            )
            ->setParameter('md', self::FORMAT_MARKDOWN)
            ->orderBy('t.name', 'ASC')
            ->addOrderBy('t.locale', 'ASC');

        if ($localeFilter !== null) {
            $qb->andWhere('t.locale = :locale')->setParameter('locale', $localeFilter);
        }
        if ($nameFilter !== null) {
            $qb->andWhere('t.name = :name')->setParameter('name', $nameFilter);
        }
        if ($skipDefault) {
            $qb->andWhere('t.name != :defaultName')->setParameter('defaultName', 'default');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Tries the conversion + validation up to `$retries` times. Each subsequent attempt feeds
     * the prior failure message back to the model so it can self-correct.
     */
    private function convertWithRetries(Template $template, string $apiKey, string $model, int $retries, OutputInterface $output): ?string
    {
        $plainSibling = $this->fetchSibling($template, 'plain');
        $datasets = $template->getDecodedTestData();
        $datasets = is_array($datasets) ? $datasets : [];

        $lastError = null;
        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            try {
                $markdown = $this->callClaude($apiKey, $model, $template, $plainSibling, $lastError);
            } catch (Throwable $e) {
                $lastError = 'API call failed: ' . $e->getMessage();
                $output->writeln(sprintf('    attempt %d: %s', $attempt, $lastError));
                continue;
            }

            $validationError = $this->validate($markdown, $datasets);
            if ($validationError === null) {
                return $markdown;
            }

            $lastError = $validationError;
            $output->writeln(sprintf('    attempt %d failed validation: %s', $attempt, $validationError));
        }

        return null;
    }

    private function fetchSibling(Template $template, string $format): ?Template
    {
        return $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Template::class, 't')
            ->where('t.locale = :locale AND t.format = :format AND t.name = :name')
            ->setParameter('locale', $template->getLocale())
            ->setParameter('format', $format)
            ->setParameter('name', $template->getName())
            ->getQuery()->getOneOrNullResult();
    }

    private function callClaude(string $apiKey, string $model, Template $template, ?Template $plainSibling, ?string $priorError): string
    {
        $userMessage = $this->buildUserMessage($template, $plainSibling, $priorError);

        $response = $this->getHttpClient()->post(self::ANTHROPIC_API_URL, [
            RequestOptions::HEADERS => [
                'x-api-key' => $apiKey,
                'anthropic-version' => self::ANTHROPIC_API_VERSION,
                'content-type' => 'application/json',
            ],
            RequestOptions::JSON => [
                'model' => $model,
                'max_tokens' => self::DEFAULT_MAX_TOKENS,
                'system' => $this->systemPrompt(),
                'messages' => [
                    ['role' => 'user', 'content' => $userMessage],
                ],
            ],
            RequestOptions::TIMEOUT => 60,
        ]);

        $body = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $blocks = $body['content'] ?? [];
        $text = '';
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'] ?? '';
            }
        }
        return $this->stripFences(trim($text));
    }

    /**
     * Strips wrapping ```markdown fences if the model adds them despite the prompt asking not to.
     */
    private function stripFences(string $text): string
    {
        if (preg_match('/^```(?:markdown|md)?\s*\n(.*)\n```\s*$/s', $text, $m) === 1) {
            return trim($m[1]);
        }
        return $text;
    }

    private function validate(string $markdown, array $datasets): ?string
    {
        if (preg_match('/<\s*(?:script|iframe|style|noscript|noframes|object|embed)\b/i', $markdown) === 1) {
            return 'output contains forbidden raw HTML tag';
        }

        $samples = $datasets === [] ? [['__empty__' => []]] : $datasets;
        foreach ($samples as $datasetName => $datasetValues) {
            $values = is_array($datasetValues) ? $datasetValues : [];
            try {
                $rendered = $this->twigRenderer->renderString($markdown, $values);
            } catch (Throwable $e) {
                return sprintf('twig render failed for dataset "%s": %s', $datasetName, $e->getMessage());
            }

            if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $rendered) === 1) {
                return sprintf('rendered output contains control characters (dataset "%s")', $datasetName);
            }

            try {
                $this->getMarkdownValidator()->convert($rendered);
            } catch (Throwable $e) {
                return sprintf('markdown convert failed for dataset "%s": %s', $datasetName, $e->getMessage());
            }
        }

        return null;
    }

    private function buildUserMessage(Template $template, ?Template $plainSibling, ?string $priorError): string
    {
        $blocks = [];
        $blocks[] = sprintf('Template name: %s', $template->getName());
        $blocks[] = sprintf('Locale: %s', $template->getLocale());
        $blocks[] = sprintf('Description: %s', (string) $template->getDescription());

        $blocks[] = "HTML/Twig source to convert:\n```\n" . $template->getSource() . "\n```";

        if ($plainSibling instanceof Template) {
            $blocks[] = "Plain-text sibling (for context only — do not copy verbatim):\n```\n" . $plainSibling->getSource() . "\n```";
        }

        try {
            $datasets = $template->getDecodedTestData();
        } catch (Throwable) {
            $datasets = null;
        }
        if (is_array($datasets) && $datasets !== []) {
            $blocks[] = "Test data (Twig placeholders are filled with these values during validation):\n```json\n"
                . json_encode($datasets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n```";
        }

        if ($priorError !== null) {
            $blocks[] = "Previous attempt failed with:\n" . $priorError . "\nProduce a corrected version.";
        }

        return implode("\n\n", $blocks);
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
        You are converting GOV.UK email templates from HTML/Twig to Markdown/Twig for delivery via
        GOV.UK Notify. Strict rules:

        1. **DO NOT TRANSLATE.** Preserve the exact wording and language of the source. Even when
           the template's locale is `cy_GB` (Welsh) but the source is in English, output the same
           English text. The source is the canonical content — your job is purely structural
           (HTML → Markdown), never linguistic. If the source says "Your application", the output
           says "Your application", never "Eich cais".
        2. Preserve every Twig directive exactly as-is: `{{ var }}`, `{{ var|raw }}`, `{% if %}`,
           `{% for %}`, `{% endfor %}`, etc. Whitespace inside the directive matters — do not change
           filter pipelines, variable names, or filter ordering.
        3. Output Notify-flavoured Markdown only. NO raw HTML tags in the output. In particular: no
           `<p>`, `<br>`, `<a>`, `<table>`, `<script>`, `<style>`, `<iframe>`. Use Markdown equivalents.
        4. No layout wrapper. Notify provides the GOV.UK email chrome (header, footer, heading style)
           server-side. Output the body content only.
        5. Convert structure:
           - `<p>` blocks → paragraphs separated by blank lines
           - `<h2>` → `## `, `<h3>` → `### ` (Notify supports up to H2 prominently; H3 is a styled subheading)
           - `<a href="X">Y</a>` → `[Y](X)`. If the href is a bare Twig `{{ url }}`, write `[Y]({{ url }})`.
           - `<strong>`/`<b>` → `**...**`, `<em>`/`<i>` → `*...*`
           - `<ul><li>` → `- item` per line; `<ol><li>` → `1. item` per line (Notify renders these correctly)
           - `<table>` → Markdown pipe table if it fits cleanly. Otherwise convert to a labelled list.
           - `<br>` → use blank line for hard break, or two spaces at end of line for soft break.
        6. Trim cosmetic differences: stray punctuation marks (e.g. `<b>foo</b` typos), extra spaces.
        7. Output the Markdown body and NOTHING ELSE — no surrounding ```markdown fences, no
           preamble like "Here is the converted template", no explanation. Just the body content.
        PROMPT;
    }

    private function buildInsertSql(Template $template, string $markdownSource): string
    {
        $columns = [
            'locale' => $template->getLocale(),
            'format' => self::FORMAT_MARKDOWN,
            'name' => $template->getName(),
            'description' => $this->markdownifyDescription((string) $template->getDescription()),
            'source' => $markdownSource,
            'category_name' => $template->getCategoryName(),
        ];

        $testDataId = $template->getTemplateTestData() !== null ? $template->getTemplateTestData()->getId() : null;
        $categoryId = $template->getCategory() !== null ? $template->getCategory()->getId() : null;

        $values = [];
        foreach ($columns as $value) {
            $values[] = $value === null ? 'NULL' : "'" . str_replace("'", "''", $value) . "'";
        }
        array_unshift($values, $testDataId === null ? 'NULL' : (string) $testDataId);
        $values[] = $categoryId === null ? 'NULL' : (string) $categoryId;

        $columnList = '`template_test_data_id`, `locale`, `format`, `name`, `description`, `source`, `category_name`, `category_id`';

        return sprintf(
            'INSERT IGNORE INTO `template` (%s) VALUES (%s);',
            $columnList,
            implode(', ', $values),
        );
    }

    /**
     * Swap "in HTML format" / "in plain format" / "template html" etc. for "Markdown" in the
     * description copied from the html sibling, so the resulting md row's description doesn't
     * lie about its own format. The patch file also runs idempotent UPDATEs for already-seeded
     * rows; this method keeps newly-generated INSERTs already-clean.
     */
    private function markdownifyDescription(string $description): string
    {
        $pairs = [
            'in HTML format' => 'in Markdown format',
            'in html format' => 'in Markdown format',
            'in plain format' => 'in Markdown format',
            'in plain text format' => 'in Markdown format',
            'locale html format' => 'locale Markdown format',
            'locale plain format' => 'locale Markdown format',
            'locale plain text format' => 'locale Markdown format',
            'template html' => 'template Markdown',
            'template plain text' => 'template Markdown',
        ];

        return strtr($description, $pairs);
    }

    private function writePatchFile(string $outPath, array $inserts): void
    {
        $dir = dirname($outPath);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Failed to create directory: ' . $dir);
        }

        $header = "-- VOL-7238: Notify-compatible Markdown template rows.\n"
            . "-- Generated by template:convert-to-md from the matching html rows.\n"
            . "-- Re-runnable: uses INSERT IGNORE keyed on (locale, format, name) unique index.\n\n";

        file_put_contents($outPath, $header . implode("\n", $inserts) . "\n");
    }

    private function getMarkdownValidator(): MarkdownConverter
    {
        if ($this->markdownValidator !== null) {
            return $this->markdownValidator;
        }

        $environment = new CommonMarkEnvironment([
            'disallowed_raw_html' => [
                'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'iframe', 'noembed', 'noframes', 'script', 'plaintext'],
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new DisallowedRawHtmlExtension());

        return $this->markdownValidator = new MarkdownConverter($environment);
    }

    private function getHttpClient(): HttpClient
    {
        return $this->httpClient ??= new HttpClient(['http_errors' => true]);
    }
}
