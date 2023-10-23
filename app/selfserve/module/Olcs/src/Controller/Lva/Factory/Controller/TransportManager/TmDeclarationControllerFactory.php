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
use Olcs\Controller\Lva\TransportManager\TmDeclarationController;
use ZfcRbac\Service\AuthorizationService;

class TmDeclarationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TmDeclarationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TmDeclarationController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $commandService = $container->get(CommandService::class);

        return new TmDeclarationController(
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
     * @return TmDeclarationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): TmDeclarationController
    {
        return $this->__invoke($serviceLocator, TmDeclarationController::class);
    }
}
