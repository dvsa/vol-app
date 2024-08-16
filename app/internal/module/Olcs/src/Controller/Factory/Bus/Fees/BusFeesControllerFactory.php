<?php

namespace Olcs\Controller\Factory\Bus\Fees;

use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Bus\Fees\BusFeesController;
use LmcRbacMvc\Identity\IdentityProviderInterface;

class BusFeesControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return BusFeesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusFeesController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $dateHelper = $container->get(DateHelperService::class);

        return new BusFeesController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $urlHelper,
            $identityProvider,
            $translationHelper,
            $dateHelper
        );
    }
}
