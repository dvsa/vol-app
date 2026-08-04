<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Command\Email;

use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Entity\Template\Template;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Iterates the `template` table and renders each row's Twig `source` against every entry in
 * its `template_test_data` JSON. Writes one text file per (template, dataset) pair so the output
 * can be diffed before/after migration steps. Used as the verification harness for VOL-7238.
 *
 *   php bin/cli.php template:render-all --out-dir=docs/vol-7238/rendered
 *   php bin/cli.php template:render-all --format=md --locale=en_GB
 */
class RenderAllTemplatesCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'template:render-all';

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
            ->setDescription('Render every email template with its test data, one file per (template, dataset).')
            ->addOption('out-dir', 'o', InputOption::VALUE_REQUIRED, 'Output directory', 'docs/vol-7238/rendered')
            ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'Filter by locale (e.g. en_GB)')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Filter by format (html, plain or md)')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Filter by template name');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        /** @var string $outDir */
        $outDir = $input->getOption('out-dir');
        $locale = $input->getOption('locale');
        $format = $input->getOption('format');
        $name = $input->getOption('name');

        $templates = $this->fetchTemplates($locale, $format, $name);

        if ($templates === []) {
            $output->writeln('<comment>No templates matched filters.</comment>');
            return Command::SUCCESS;
        }

        $successes = 0;
        $failures = [];

        foreach ($templates as $template) {
            $rowKey = sprintf('%s/%s/%s', $template->getLocale(), $template->getFormat(), $template->getName());
            try {
                $datasets = $template->getDecodedTestData();
            } catch (Throwable $e) {
                $failures[] = $rowKey . ' [test data decode]: ' . $e->getMessage();
                continue;
            }

            if (!is_array($datasets) || $datasets === []) {
                $failures[] = $rowKey . ' [test data empty]';
                continue;
            }

            $source = (string) $template->getSource();

            foreach ($datasets as $datasetName => $datasetValues) {
                $datasetValues = is_array($datasetValues) ? $datasetValues : [];
                try {
                    $rendered = $this->twigRenderer->renderString($source, $datasetValues);
                } catch (Throwable $e) {
                    $failures[] = $rowKey . ' [' . $datasetName . ']: ' . $e->getMessage();
                    continue;
                }

                $path = $this->buildOutputPath($outDir, $template, (string) $datasetName);
                $this->writeFile($path, $rendered);
                $successes++;
            }
        }

        $output->writeln(sprintf('<info>Rendered %d outputs into %s</info>', $successes, $outDir));

        if ($failures !== []) {
            $output->writeln(sprintf('<error>%d failure(s):</error>', count($failures)));
            foreach ($failures as $message) {
                $output->writeln('  - ' . $message);
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return Template[]
     */
    private function fetchTemplates(?string $locale, ?string $format, ?string $name): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Template::class, 't')
            ->orderBy('t.name', 'ASC')
            ->addOrderBy('t.locale', 'ASC')
            ->addOrderBy('t.format', 'ASC');

        if ($locale !== null) {
            $qb->andWhere('t.locale = :locale')->setParameter('locale', $locale);
        }
        if ($format !== null) {
            $qb->andWhere('t.format = :format')->setParameter('format', $format);
        }
        if ($name !== null) {
            $qb->andWhere('t.name = :name')->setParameter('name', $name);
        }

        return $qb->getQuery()->getResult();
    }

    private function buildOutputPath(string $outDir, Template $template, string $datasetName): string
    {
        $safeDataset = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $datasetName) ?? $datasetName;
        return sprintf(
            '%s/%s/%s/%s__%s.txt',
            rtrim($outDir, '/'),
            $template->getLocale(),
            $template->getFormat(),
            $template->getName(),
            $safeDataset,
        );
    }

    private function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Failed to create directory: ' . $dir);
        }
        file_put_contents($path, $content);
    }
}
