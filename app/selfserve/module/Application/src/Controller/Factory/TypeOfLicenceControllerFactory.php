<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Application\Controller\TypeOfLicenceController;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Service\AuthorizationService;

class TypeOfLicenceControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TypeOfLicenceController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TypeOfLicenceController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $formHelper = $container->get(FormHelperService::class);

        return new TypeOfLicenceController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $scriptFactory,
            $formServiceManager,
            $transferAnnotationBuilder,
            $commandService,
            $translationHelper,
            $restrictionHelper,
            $stringHelper,
            $formHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TypeOfLicenceController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): TypeOfLicenceController
    {
        return $this->__invoke($serviceLocator, TypeOfLicenceController::class);
    }
}
