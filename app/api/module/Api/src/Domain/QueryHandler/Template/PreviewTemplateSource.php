<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Email\View\NotifyChrome;
use Dvsa\Olcs\Transfer\Query\Template\PreviewTemplateSource as PreviewTemplateSourceQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Exception;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Preview template source
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PreviewTemplateSource extends AbstractQueryHandler
{
    public const FORMAT_MARKDOWN = 'md';

    protected $repoServiceName = 'Template';

    /** @var TwigRenderer */
    private $twigRenderer;

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

        $result = [];
        foreach ($datasets as $datasetName => $datasetValues) {
            try {
                $rendered = $this->twigRenderer->renderString($source, $datasetValues);
                $result[$datasetName] = $format === self::FORMAT_MARKDOWN
                    ? $this->renderMarkdownPreview($rendered, $description)
                    : $this->strategySelectingViewRenderer->render(
                        $locale,
                        $format,
                        'default',
                        ['content' => $rendered]
                    );
            } catch (Exception $e) {
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
        $html = $this->markdownConverter->convert($markdown)->getContent();

        // Shared chrome with DevNotifyTransport so the admin preview matches Mailpit output.
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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->twigRenderer = $container->get('TemplateTwigRenderer');
        $this->strategySelectingViewRenderer = $container->get('TemplateStrategySelectingViewRenderer');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
