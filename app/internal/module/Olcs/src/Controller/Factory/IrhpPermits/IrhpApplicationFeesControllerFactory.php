<?php

namespace Olcs\Controller\Factory\IrhpPermits;

use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\IrhpPermits\IrhpApplicationFeesController;
use LmcRbacMvc\Identity\IdentityProviderInterface;

class IrhpApplicationFeesControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return IrhpApplicationFeesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpApplicationFeesController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $dateHelper = $container->get(DateHelperService::class);

        return new IrhpApplicationFeesController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $urlHelper,
            $identityProvider,
            $translationHelper,
            $dateHelper
        );
    }
}
