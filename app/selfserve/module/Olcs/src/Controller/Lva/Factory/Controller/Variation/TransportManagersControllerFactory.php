<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Adapters\VariationTransportManagerAdapter;
use Olcs\Controller\Lva\Variation\TransportManagersController;
use ZfcRbac\Service\AuthorizationService;

class TransportManagersControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TransportManagersController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagersController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $queryService = $container->get(QueryService::class);
        $commandService = $container->get(CommandService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $transportManagerHelper = $container->get(TransportManagerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $lvaAdapter = $container->get(VariationTransportManagerAdapter::class);
        $tableFactory = $container->get(TableFactory::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);

        return new TransportManagersController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $scriptFactory,
            $queryService,
            $commandService,
            $transferAnnotationBuilder,
            $transportManagerHelper,
            $translationHelper,
            $lvaAdapter,
            $tableFactory,
            $uploadHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TransportManagersController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): TransportManagersController
    {
        return $this->__invoke($serviceLocator, TransportManagersController::class);
    }
}
