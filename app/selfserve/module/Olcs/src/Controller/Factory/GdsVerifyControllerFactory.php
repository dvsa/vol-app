<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\Cache\Storage\Adapter\Redis;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\GdsVerifyController;
use LmcRbacMvc\Service\AuthorizationService;

class GdsVerifyControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GdsVerifyController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GdsVerifyController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $redis = $container->get(Redis::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new GdsVerifyController(
            $niTextTranslationUtil,
            $authService,
            $redis,
            $formHelper,
            $scriptFactory,
            $flashMessengerHelper
        );
    }
}
