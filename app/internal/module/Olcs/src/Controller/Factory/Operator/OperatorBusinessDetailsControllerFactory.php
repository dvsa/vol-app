<?php

namespace Olcs\Controller\Factory\Operator;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Operator\OperatorBusinessDetailsController;
use Olcs\Controller\Operator\OperatorController;
use Olcs\Service\Data\Licence;

class OperatorBusinessDetailsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return OperatorController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatorBusinessDetailsController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $dateHelper = $container->get(DateHelperService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $licenceDataService = $container->get(Licence::class);
        $queryService = $container->get(QueryService::class);
        $navigation = $container->get('Navigation');
        $translationHelper = $container->get(TranslationHelperService::class);

        return new OperatorBusinessDetailsController(
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
            $translationHelper
        );
    }
}
