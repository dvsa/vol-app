<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Dvsa\Olcs\Api\Domain\Repository\Replacement as ReplacementRepo;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as TranslationKeyTextRepo;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Framework\Support\ApiApplication;
use Dvsa\OlcsTest\Api\Framework\Support\Entrypoint;
use Dvsa\OlcsTest\Api\Framework\Support\FrameworkTestCase;
use Dvsa\OlcsTest\Api\Framework\Support\JsonSnapshot;
use Dvsa\OlcsTest\Api\Framework\Support\TextSnapshot;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\I18n\View\Helper\Translate;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Doctype;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Mockery as m;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use Twig\Loader\ArrayLoader;

/**
 * The HTML and text the api renders from .phtml templates, byte for byte.
 *
 * The api renders stored documents and emails server-side: application, continuation, permit and
 * conversation snapshots (module/Snapshot), permit answer summaries and submission section defaults
 * (module/Api/view), and every SMTP email body (module/Email/view). All of it goes through the `ViewRenderer`
 * service, its template resolver and its `ViewHelperManager` — and today laminas-mvc registers all three
 * (Laminas\Mvc\Service\ServiceListenerFactory) and configures the helpers from `view_manager` config
 * (ViewHelperManagerFactory: `doctype`, `basePath`, `url`). The Scanning module's `doctype => HTML5` is what
 * makes every review snapshot open with `<!DOCTYPE html>`.
 *
 * When the laminas-mvc removal re-registers those services, this test is what proves the stored documents
 * and the emails still come out the same: which file each template name resolves to, which class each helper
 * the templates call resolves to, and the rendered output of every template family.
 */
final class ViewRenderingTest extends FrameworkTestCase
{
    private const string SNAPSHOTS = __DIR__ . '/snapshots/views';
    private const string UPDATE_COMMAND = 'UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter ViewRenderingTest';

    /**
     * Where the template files live. Every .phtml under these must resolve by at least one name.
     */
    private const array TEMPLATE_DIRECTORIES = [
        'module/Api/view',
        'module/Db/view',
        'module/Email/view',
        'module/Scanning/view',
        'module/Snapshot/view',
        'module/Snapshot/config/language/partials',
    ];

    public function testEveryTemplateNameResolvesToTheSameFile(): void
    {
        $app = $this->boot();
        $renderer = $this->renderer($app);
        $viewManager = $app->serviceManager->get('config')['view_manager'];

        $names = array_keys($viewManager['template_map'] ?? []);

        foreach ($viewManager['template_path_stack'] ?? [] as $path) {
            foreach (self::templateFiles((string) realpath($path)) as $file) {
                $names[] = substr($file, strlen((string) realpath($path)) + 1, -strlen('.phtml'));
            }
        }

        $names = array_unique($names);
        sort($names);

        $resolved = [];

        foreach ($names as $name) {
            $file = $renderer->resolver()->resolve($name, $renderer);
            $resolved[$name] = is_string($file) ? self::relative($file) : null;
        }

        $unreachable = [];

        foreach (self::TEMPLATE_DIRECTORIES as $directory) {
            foreach (self::templateFiles(ApiApplication::root() . '/' . $directory) as $file) {
                if (!in_array(self::relative($file), $resolved, true)) {
                    $unreachable[] = self::relative($file);
                }
            }
        }

        JsonSnapshot::assertMatches(
            self::SNAPSHOTS . '/templates.json',
            ['resolves' => $resolved, 'unreachable' => $unreachable],
            'Template resolution',
            self::UPDATE_COMMAND
        );
    }

    public function testEveryHelperTheTemplatesCallResolvesToTheSameHelper(): void
    {
        $app = $this->boot([]);
        $helpers = $this->renderer($app)->getHelperPluginManager();
        assert($helpers instanceof HelperPluginManager);

        $names = [];

        foreach (self::TEMPLATE_DIRECTORIES as $directory) {
            foreach (self::templateFiles(ApiApplication::root() . '/' . $directory) as $file) {
                preg_match_all('/\$this->([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', (string) file_get_contents($file), $matches);
                array_push($names, ...$matches[1]);
            }
        }

        // render() and friends belong to the renderer itself, not the helper manager
        $names = array_filter(
            array_unique($names),
            static fn(string $name): bool => !method_exists(PhpRenderer::class, $name)
        );
        sort($names);

        $resolved = [];

        foreach ($names as $name) {
            $helper = $helpers->get($name);
            $resolved[$name] = is_object($helper) ? $helper::class : get_debug_type($helper);

            // Configured from view_manager.doctype by laminas-mvc's ViewHelperManagerFactory
            if ($helper instanceof Doctype) {
                $resolved[$name] .= ' (' . $helper->getDoctype() . ')';
            }
        }

        JsonSnapshot::assertMatches(self::SNAPSHOTS . '/helpers.json', $resolved, 'View helpers', self::UPDATE_COMMAND);
        $this->assertNothingLogged($app);
    }

    /**
     * @return iterable<string, array{string, string, \Closure(): array<string, mixed>}>
     */
    public static function snapshotDocuments(): iterable
    {
        foreach (ViewRenderingFixtures::snapshotDocuments() as $case => [$layout, $variables]) {
            yield $case => [$case, $layout, $variables];
        }
    }

    /**
     * One application per document, as a web request generating one snapshot has: see
     * testHeadHelpersCarryOverFromOneRenderToTheNext for why a shared one would change the output.
     *
     * @param \Closure(): array<string, mixed> $variables
     */
    #[DataProvider('snapshotDocuments')]
    public function testSnapshotDocumentsRenderUnchanged(string $case, string $layout, \Closure $variables): void
    {
        $app = $this->boot(['en_GB']);

        TextSnapshot::assertFileMatches(
            self::snapshotDocumentFile($case),
            $this->generator($app)->render($variables(), $layout),
            'Snapshot document ' . $case,
            self::UPDATE_COMMAND
        );
        $this->assertNothingLogged($app);
    }

    public function testEverySnapshotDocumentSnapshotHasACase(): void
    {
        JsonSnapshot::assertNoOrphans(
            self::SNAPSHOTS . '/snapshot-documents',
            array_map(self::snapshotDocumentFile(...), array_keys(ViewRenderingFixtures::snapshotDocuments())),
            self::UPDATE_COMMAND
        );
    }

    /**
     * The head helpers (headTitle, headMeta, headLink, headScript) are shared through the container and keep
     * what they are given, so a second document rendered by the same process repeats the first one's title and
     * stylesheet link. A web request renders one document; a long-running CLI process (the queue processor)
     * can render several. Pinned as the current behaviour, not as a desirable one: a bootstrap that creates
     * helpers per render would change these documents, and that change should be made on purpose.
     */
    public function testHeadHelpersCarryOverFromOneRenderToTheNext(): void
    {
        $app = $this->boot(['en_GB']);
        $generator = $this->generator($app);
        $variables = ['reviewTitle' => 'first', 'subTitle' => 'first', 'settings' => [], 'sections' => []];

        $heads = [];

        foreach (['first', 'second'] as $render) {
            preg_match('#<head>.*</head>#s', $generator->render($variables, 'review'), $match);
            $heads[$render] = $match[0] ?? null;
        }

        JsonSnapshot::assertMatches(
            self::SNAPSHOTS . '/repeated-render-heads.json',
            $heads,
            'Repeated render',
            self::UPDATE_COMMAND
        );
        $this->assertNothingLogged($app);
    }

    /**
     * `markup-*` translation keys render a locale-scoped partial (olcs-utils MissingTranslationProcessor, which
     * resolves `<locale>/<key>` through the same resolver). Every partial, in its own locale, plus the NI
     * locale's fallback to the GB partial.
     */
    public function testMarkupTranslationsRenderTheirPartials(): void
    {
        $app = $this->boot(['en_GB', 'cy_GB', 'en_NI']);
        // The translator the templates use: the one the translate helper was given
        $helper = $this->renderer($app)->getHelperPluginManager()->get('translate');
        assert($helper instanceof Translate);
        $translator = $helper->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $directory = ApiApplication::root() . '/module/Snapshot/config/language/partials';
        $rendered = [];

        foreach (self::templateFiles($directory) as $file) {
            [$locale, $key] = explode('/', substr($file, strlen($directory) + 1, -strlen('.phtml')));
            $rendered[$locale . '/' . $key . TextSnapshot::EXTENSION]
                = $translator->translate($key, 'default', $locale);
        }

        $rendered['en_NI/markup-generic-return-address' . TextSnapshot::EXTENSION]
            = $translator->translate('markup-generic-return-address', 'default', 'en_NI');

        TextSnapshot::assertDirectoryMatches(
            self::SNAPSHOTS . '/markup',
            $rendered,
            'Markup translations',
            self::UPDATE_COMMAND
        );
        $this->assertNothingLogged($app);
    }

    public function testPermitAnswerSummariesAndSubmissionDefaultsRenderUnchanged(): void
    {
        $app = $this->boot(['en_GB']);
        $renderer = $this->renderer($app);
        $rendered = [];

        foreach (ViewRenderingFixtures::answersSummaries() as $case => [$template, $variables]) {
            $rendered['answers-summary/' . $case . '.html' . TextSnapshot::EXTENSION]
                = $renderer->render($template, $variables);
        }

        // Rendered by the production section generators, by their own template names ('/sections/<name>.phtml')
        $sections = $app->serviceManager->get(SectionGeneratorPluginManager::class);
        assert($sections instanceof SectionGeneratorPluginManager);

        foreach (['applicants-comments', 'applicants-responses'] as $section) {
            $data = $sections->get($section)->generateSection(m::mock(Cases::class));
            $rendered['submission-sections/' . $section . '.html' . TextSnapshot::EXTENSION] = $data['data']['text'];
        }

        TextSnapshot::assertDirectoryMatches(self::SNAPSHOTS . '/api', $rendered, 'Api views', self::UPDATE_COMMAND);
        $this->assertNothingLogged($app);
    }

    /**
     * Every file-based email template, in both languages and both formats, and every email layout, rendered by
     * the same StrategySelectingViewRenderer the Email module's TemplateRenderer uses. No database (Twig)
     * templates are offered, so every one comes from its .phtml file, as for an SMTP email. Notify mode renders
     * markdown templates from the database instead and is not covered here.
     *
     * Templates and layouts are stored separately, so a layout change shows once rather than in every email;
     * a few complete bodies from TemplateRenderer::renderBody() pin how the two are put together.
     */
    public function testEmailsRenderUnchanged(): void
    {
        $app = $this->boot(['en_GB'], static function (ServiceManager $serviceManager): void {
            $serviceManager->setService('TemplateDatabaseTwigLoader', new ArrayLoader([]));
        });

        $strategy = $app->serviceManager->get('TemplateStrategySelectingViewRenderer');
        assert($strategy instanceof StrategySelectingViewRenderer);
        $renderer = $app->serviceManager->get(TemplateRenderer::class);
        assert($renderer instanceof TemplateRenderer);
        $this->assertFalse($renderer->isNotifyMode(), 'The stubbed mail DSN should select SMTP mode');

        $templateDirectory = ApiApplication::root() . '/module/Email/view/email';
        $templates = [];

        foreach (self::templateFiles($templateDirectory) as $file) {
            $templates[basename($file, '.phtml')][] = $file;
        }

        ksort($templates);
        $rendered = [];

        foreach ($templates as $template => $files) {
            $variables = ViewRenderingFixtures::emailVariables($template, $files);

            foreach ($files as $file) {
                // <locale>/<format>/<template>, the name TemplateRenderer asks for
                [$locale, $format] = explode('/', substr($file, strlen($templateDirectory) + 1));
                $rendered[sprintf('templates/%s/%s/%s%s', $locale, $format, $template, TextSnapshot::EXTENSION)]
                    = $strategy->render($locale, $format, $template, $variables);
            }
        }

        $layoutDirectory = ApiApplication::root() . '/module/Email/view/layout';

        foreach (self::templateFiles($layoutDirectory) as $file) {
            $name = substr($file, strlen($layoutDirectory) + 1, -strlen('.phtml'));
            [$locale, $format, $layout] = explode('/', $name);
            $rendered['layouts/' . $name . TextSnapshot::EXTENSION] = $strategy->render(
                $locale,
                $format,
                $layout,
                ['content' => ViewRenderingFixtures::placeholder('content')]
            );
        }

        // Whole bodies, as sent: a template in the default layout in both languages, and the inspection
        // request, which has no HTML version and is sent in the blank layout
        $bodies = [
            ['ebsr-cancelled', 'en_GB', 'default'],
            ['ebsr-cancelled', 'cy_GB', 'default'],
            ['inspection-request', 'en_GB', 'blank'],
        ];

        foreach ($bodies as [$template, $locale, $layout]) {
            $message = new Message('operator@example.com', 'Subject');
            $message->setLocale($locale);
            $message->setHasHtml($layout === 'default');

            $variables = ViewRenderingFixtures::emailVariables($template, $templates[$template]);
            $renderer->renderBody($message, $template, $variables, $layout);

            $file = sprintf('bodies/%s/%s', $locale, $template);
            $rendered[$file . '.plain' . TextSnapshot::EXTENSION] = $message->getPlainBody();

            if ($message->getHasHtml()) {
                $rendered[$file . '.html' . TextSnapshot::EXTENSION] = $message->getHtmlBody();
            }
        }

        TextSnapshot::assertDirectoryMatches(self::SNAPSHOTS . '/email', $rendered, 'Emails', self::UPDATE_COMMAND);
        $this->assertNothingLogged($app);
    }

    /**
     * Boots the web application with a fixed set of translations.
     *
     * The translator's loader is the real TranslationLoader, reading from a stand-in cache: it loads the
     * replacement table once, when the translator is created, then each locale's messages once, on first use.
     *
     * @param list<string>|null $locales the locales the test translates in; null when nothing is translated
     * @param (\Closure(ServiceManager): void)|null $configure further overrides
     */
    private function boot(?array $locales = null, ?\Closure $configure = null): ApiApplication
    {
        $loader = null;

        if ($locales !== null) {
            $cache = m::mock(CacheEncryption::class);
            $cache->expects('getCustomItem')
                ->with(CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER)
                ->andReturn([ViewRenderingFixtures::REPLACEMENT_TOKEN => 'replaced <&>']);

            foreach ($locales as $locale) {
                $cache->expects('getCustomItem')
                    ->with(CacheEncryption::TRANSLATION_KEY_IDENTIFIER, $locale)
                    ->andReturn(ViewRenderingFixtures::translations($locale));
            }

            // Its repositories are only read if the cache comes back empty
            $loader = new TranslationLoader(
                $cache,
                m::mock(TranslationKeyTextRepo::class),
                m::mock(ReplacementRepo::class)
            );
        }

        return ApiApplication::bootstrap(
            Entrypoint::Web,
            static function (ServiceManager $serviceManager) use ($loader, $configure): void {
                if ($loader !== null) {
                    $translatorPlugins = $serviceManager->get('TranslatorPluginManager');
                    assert($translatorPlugins instanceof LoaderPluginManager);
                    $translatorPlugins->setAllowOverride(true);
                    $translatorPlugins->setService(TranslationLoader::class, $loader);
                    $translatorPlugins->setAllowOverride(false);
                }

                if ($configure !== null) {
                    $configure($serviceManager);
                }
            }
        );
    }

    private function renderer(ApiApplication $app): PhpRenderer
    {
        $renderer = $app->serviceManager->get('ViewRenderer');
        assert($renderer instanceof PhpRenderer);

        return $renderer;
    }

    /**
     * A Snapshot generator built from the container's AbstractGeneratorServices, as every generator is, so its
     * generateReadonly() — the one method every snapshot document goes through — can be called directly.
     */
    private function generator(ApiApplication $app): object
    {
        $services = $app->serviceManager->get(AbstractGeneratorServices::class);
        assert($services instanceof AbstractGeneratorServices);

        return new class ($services) extends AbstractGenerator {
            /**
             * @param array<string, mixed> $config
             */
            public function render(array $config, string $layout): string
            {
                return $this->generateReadonly($config, $layout);
            }
        };
    }

    /**
     * Rendering logs nothing. Anything logged would also start a PHP session: the log's SessionId processor
     * starts one to read its id.
     */
    private function assertNothingLogged(ApiApplication $app): void
    {
        $this->assertSame(
            [],
            array_map(
                static fn(LogRecord $record): string => $record->level->getName() . ': ' . $record->message,
                $app->logs->getRecords()
            )
        );
    }

    private static function snapshotDocumentFile(string $case): string
    {
        return self::SNAPSHOTS . '/snapshot-documents/' . $case . '.html' . TextSnapshot::EXTENSION;
    }

    /**
     * @return list<string> absolute paths, sorted
     */
    private static function templateFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            assert($file instanceof \SplFileInfo);

            if ($file->getExtension() === 'phtml') {
                $files[] = (string) $file->getRealPath();
            }
        }

        sort($files);

        return $files;
    }

    private static function relative(string $file): string
    {
        $real = (string) realpath($file);
        $root = (string) realpath(ApiApplication::root());

        return str_starts_with($real, $root . '/') ? substr($real, strlen($root) + 1) : $real;
    }
}
