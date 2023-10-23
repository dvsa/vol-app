<?php

namespace Olcs\Controller\Lva\Factory\Controller\TransportManager;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\TransportManager\CheckAnswersController;
use ZfcRbac\Service\AuthorizationService;

class CheckAnswersControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CheckAnswersController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CheckAnswersController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);

        return new CheckAnswersController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
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
     * @return CheckAnswersController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): CheckAnswersController
    {
        return $this->__invoke($serviceLocator, CheckAnswersController::class);
    }
}
