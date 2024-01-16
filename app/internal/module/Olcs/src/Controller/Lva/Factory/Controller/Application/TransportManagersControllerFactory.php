<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\Controller\Lva\Adapters\ApplicationTransportManagerAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\TransportManagersController;
use LmcRbacMvc\Service\AuthorizationService;

class TransportManagersControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TransportManagersController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagersController
    {
        
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
        $stringHelper = $container->get(StringHelperService::class);
        $lvaAdapter = $container->get(ApplicationTransportManagerAdapter::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $navigation = $container->get('Navigation');

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
            $stringHelper,
            $lvaAdapter,
            $restrictionHelper,
            $navigation
        );
    }
}
