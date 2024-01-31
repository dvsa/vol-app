<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\CommunityLicencesController;
use LmcRbacMvc\Service\AuthorizationService;

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

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $commandService = $container->get(CommandService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $navigation = $container->get('navigation');

        return new CommunityLicencesController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $transferAnnotationBuilder,
            $commandService,
            $stringHelper,
            $restrictionHelper,
            $navigation
        );
    }
}
