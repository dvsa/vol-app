<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Licence\CommunityLicencesController;
use ZfcRbac\Service\AuthorizationService;

class CommunityLicencesControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return CommunityLicencesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CommunityLicencesController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $commandService = $container->get(CommandService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $navigation = $container->get('Navigation');

        return new CommunityLicencesController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $transferAnnotationBuilder,
            $commandService,
            $navigation
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CommunityLicencesController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): CommunityLicencesController
    {
        return $this->__invoke($serviceLocator, CommunityLicencesController::class);
    }
}
