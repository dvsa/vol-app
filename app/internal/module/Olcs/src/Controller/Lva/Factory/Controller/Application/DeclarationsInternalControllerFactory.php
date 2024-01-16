<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\DeclarationsInternalController;
use LmcRbacMvc\Service\AuthorizationService;

class DeclarationsInternalControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return DeclarationsInternalController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DeclarationsInternalController
    {
        
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $stringHelper = $container->get(StringHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $navigation = $container->get('Navigation');

        return new DeclarationsInternalController(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
            $translationHelper,
            $flashMessengerHelper,
            $stringHelper,
            $restrictionHelper,
            $navigation
        );
    }
}
