<?php

namespace Olcs\Controller\Lva\Factory\Controller\TransportManager;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use OLCS\Controller\Lva\TransportManager\ConfirmationController;
use ZfcRbac\Service\AuthorizationService;

class ConfirmationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ConfirmationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConfirmationController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);

        return new ConfirmationController(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $transferAnnotationBuilder,
            $commandService
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ConfirmationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ConfirmationController
    {
        return $this->__invoke($serviceLocator, ConfirmationController::class);
    }
}
