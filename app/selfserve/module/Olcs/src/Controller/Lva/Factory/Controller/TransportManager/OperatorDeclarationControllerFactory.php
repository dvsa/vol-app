<?php

namespace Olcs\Controller\Lva\Factory\Controller\TransportManager;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\TransportManager\OperatorDeclarationController;
use LmcRbacMvc\Service\AuthorizationService;

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
}
