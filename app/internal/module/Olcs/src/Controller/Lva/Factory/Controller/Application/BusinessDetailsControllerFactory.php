<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\BusinessDetailsController;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use LmcRbacMvc\Service\AuthorizationService;

class BusinessDetailsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return BusinessDetailsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusinessDetailsController
    {
        
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $tableFactory = $container->get(TableFactory::class);
        $fileUploadHelper = $container->get(FileUploadHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $navigation = $container->get('Navigation');

        return new BusinessDetailsController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $identityProvider,
            $stringHelper,
            $tableFactory,
            $fileUploadHelper,
            $restrictionHelper,
            $navigation
        );
    }
}
