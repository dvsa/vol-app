<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\PublishController;
use LmcRbacMvc\Service\AuthorizationService;

class PublishControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return PublishController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PublishController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $navigation = $container->get('navigation');
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new PublishController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $stringHelper,
            $formServiceManager,
            $navigation,
            $flashMessengerHelper
        );
    }
}
