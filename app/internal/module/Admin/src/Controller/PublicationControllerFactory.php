<?php

namespace Admin\Controller;

use Common\Auth\Service\RefreshTokenService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Psr\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;
use Olcs\Service\WebDav\WebDavRedisFactory;

class PublicationControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PublicationController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $webDavJsonWebTokenGenerationService = $container->get(WebDavJsonWebTokenGenerationService::class);
        assert($webDavJsonWebTokenGenerationService instanceof WebDavJsonWebTokenGenerationService);

        $redis = $container->get(WebDavRedisFactory::SERVICE_NAME);
        $refreshTokenService = $container->get(RefreshTokenService::class);

        return new PublicationController(
            $translationHelper,
            $formHelperService,
            $flashMessenger,
            $navigation,
            $webDavJsonWebTokenGenerationService,
            $redis,
            $refreshTokenService,
        );
    }
}
