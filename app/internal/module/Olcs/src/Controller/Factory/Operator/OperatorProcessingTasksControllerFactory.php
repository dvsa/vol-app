<?php

namespace Olcs\Controller\Factory\Operator;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Data\PluginManager;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Operator\OperatorProcessingTasksController;
use Olcs\Service\Data\Licence;
use Olcs\Service\Data\SubCategory;
use Psr\Container\ContainerInterface;

class OperatorProcessingTasksControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return OperatorProcessingTasksController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatorProcessingTasksController
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
        $subCategoryDataService = $container->get(PluginManager::class)->get(SubCategory::class);

        return new OperatorProcessingTasksController(
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
            $subCategoryDataService
        );
    }
}
