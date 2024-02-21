<?php

namespace Admin\Controller;

use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Identity\IdentityProviderInterface;

class PaymentProcessingFeesControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PaymentProcessingFeesController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $dateHelper = $container->get(DateHelperService::class);

        return new PaymentProcessingFeesController(
            $scriptFactory,
            $tableFactory,
            $formHelper,
            $urlHelper,
            $identityProvider,
            $translationHelper,
            $dateHelper
        );
    }
}
