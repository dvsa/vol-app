<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\Sms\SmsLength;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Email\View\NotifyChrome;
use Dvsa\Olcs\Transfer\Query\Template\PreviewTemplateSource as PreviewTemplateSourceQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * Preview template source
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PreviewTemplateSource extends AbstractQueryHandler
{
    public const FORMAT_MARKDOWN = 'md';
    public const FORMAT_SMS = 'sms';

    protected $repoServiceName = 'Template';

    /** @var TwigRenderer */
    private $twigRenderer;

    /** SMS is plain text, so it is rendered without HTML escaping */
    private TwigRenderer $plainTextTwigRenderer;

    /** @var StrategySelectingViewRenderer */
    private $strategySelectingViewRenderer;

    private ?GithubFlavoredMarkdownConverter $markdownConverter = null;

    /**
     * Handle query
     *
     * @param QueryInterface|PreviewTemplateSourceQry $query query
     *
     * @return array
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $source = $query->getSource();

        $template = $this->getRepo()->fetchUsingId($query);
        $datasets = $template->getDecodedTestData();
        $locale = $template->getLocale();
        $format = $template->getFormat();
        $description = (string) $template->getDescription();

        if (!is_array($datasets)) {
            return ['error' => true, 'Test data' => 'Test data is not valid JSON'];
        }

        $result = [];
        foreach ($datasets as $datasetName => $datasetValues) {
            // Test data must be {"<set name>": {<variables>}}, not a flat list of variables.
            if (!is_array($datasetValues)) {
                return [
                    'error' => true,
                    $datasetName => sprintf('Test data set "%s" is not a set of variables', $datasetName),
                ];
            }
            try {
                if ($format === self::FORMAT_SMS) {
                    // Plain text as sent, plus how many texts it will use.
                    $rendered = $this->plainTextTwigRenderer->renderString($source, $datasetValues);
                    $result[$datasetName] = ['text' => $rendered] + SmsLength::estimate($rendered);
                    continue;
                }
                $rendered = $this->twigRenderer->renderString($source, $datasetValues);
                $result[$datasetName] = $format === self::FORMAT_MARKDOWN
                    ? $this->renderMarkdownPreview($rendered, $description)
                    : $this->strategySelectingViewRenderer->render(
                        $locale,
                        $format,
                        'default',
                        ['content' => $rendered]
                    );
            } catch (Throwable $e) {
                $result['error'] = true;
                $result[$datasetName] = $e->getMessage();
                break;
            }
        }

        return $result;
    }

    private function renderMarkdownPreview(string $markdown, string $subject): string
    {
        $this->markdownConverter ??= new GithubFlavoredMarkdownConverter();

        // Shared Notify-style rendering + chrome with DevNotifyTransport, so the admin preview
        // matches both Mailpit and what GOV.UK Notify actually delivers.
        $html = NotifyChrome::renderMarkdownBody($markdown, $this->markdownConverter);

        return NotifyChrome::wrap($html, $subject);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PreviewTemplateSource
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $fullContainer = $container;

        $this->twigRenderer = $container->get('TemplateTwigRenderer');
        $this->plainTextTwigRenderer = $container->get('TemplatePlainTextTwigRenderer');
        $this->strategySelectingViewRenderer = $container->get('TemplateStrategySelectingViewRenderer');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
