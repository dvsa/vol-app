<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Application\Controller\UndertakingsController;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Service\AuthorizationService;

class UndertakingsControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return UndertakingsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UndertakingsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);

        return new UndertakingsController(
            $niTextTranslationUtil,
            $authService,
            $scriptFactory,
            $transferAnnotationBuilder,
            $commandService,
            $flashMessengerHelper,
            $formHelper,
            $translationHelper,
            $restrictionHelper,
            $stringHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UndertakingsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): UndertakingsController
    {
        return $this->__invoke($serviceLocator, UndertakingsController::class);
    }
}
