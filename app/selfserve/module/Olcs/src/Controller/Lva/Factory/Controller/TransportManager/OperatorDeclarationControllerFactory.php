<?php

namespace Olcs\Controller\Lva\Factory\Controller\TransportManager;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\TransportManager\OperatorDeclarationController;
use ZfcRbac\Service\AuthorizationService;

class OperatorDeclarationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return OperatorDeclarationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatorDeclarationController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);

        return new OperatorDeclarationController(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $formHelper,
            $scriptFactory,
            $transferAnnotationBuilder,
            $commandService
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OperatorDeclarationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): OperatorDeclarationController
    {
        return $this->__invoke($serviceLocator, OperatorDeclarationController::class);
    }
}
