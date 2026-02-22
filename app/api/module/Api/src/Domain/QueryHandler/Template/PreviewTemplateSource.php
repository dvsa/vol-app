<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\Template\StrategySelectingViewRenderer;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Transfer\Query\Template\PreviewTemplateSource as PreviewTemplateSourceQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Exception;
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
    protected $repoServiceName = 'Template';

    /** @var TwigRenderer */
    private $twigRenderer;

    /** @var StrategySelectingViewRenderer */
    private $strategySelectingViewRenderer;

    /**
     * Handle query
     *
     * @param QueryInterface|PreviewTemplateSourceQry $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $source = $query->getSource();

        $template = $this->getRepo()->fetchUsingId($query);
        $datasets = $template->getDecodedTestData();
        $locale = $template->getLocale();
        $format = $template->getFormat();

        $result = [];
        foreach ($datasets as $datasetName => $datasetValues) {
            try {
                $result[$datasetName] = $this->strategySelectingViewRenderer->render(
                    $locale,
                    $format,
                    'default',
                    ['content' => $this->twigRenderer->renderString($source, $datasetValues)]
                );
            } catch (Exception $e) {
                $result['error'] = true;
                $result[$datasetName] = $e->getMessage();
                break;
            }
        }

        return $result;
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
