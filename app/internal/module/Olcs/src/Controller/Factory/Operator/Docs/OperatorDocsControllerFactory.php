<?php

namespace Olcs\Controller\Factory\Operator\Docs;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Data\PluginManager;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Operator\Docs\OperatorDocsController;
use Olcs\Service\Data\DocumentSubCategory;
use Olcs\Service\Data\Licence;
use Psr\Container\ContainerInterface;

class OperatorDocsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return OperatorDocsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatorDocsController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $dateHelper = $container->get(DateHelperService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $licenceDataService = $container->get(PluginManager::class)->get(Licence::class);
        $queryService = $container->get(QueryService::class);
        $navigation = $container->get('navigation');
        $docSubCategoryDataService = $container->get(PluginManager::class)->get(DocumentSubCategory::class);
        $translationHelper = $container->get(TranslationHelperService::class);

        return new OperatorDocsController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $dateHelper,
            $transferAnnotationBuilder,
            $commandService,
            $flashMessengerHelper,
            $licenceDataService,
            $queryService,
            $navigation,
            $docSubCategoryDataService,
            $translationHelper
        );
    }
}
