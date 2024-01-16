<?php

namespace Olcs\Controller\Factory\Licence\Fees;

use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Licence\Fees\LicenceFeesController;
use LmcRbacMvc\Identity\IdentityProviderInterface;

class LicenceFeesControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return LicenceFeesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceFeesController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $oppositionHelper = $container->get(OppositionHelperService::class);
        $complaintsHelper = $container->get(ComplaintsHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $dateHelper = $container->get(DateHelperService::class);
        $navigation = $container->get('Navigation');

        return new LicenceFeesController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $oppositionHelper,
            $complaintsHelper,
            $flashMessengerHelper,
            $urlHelper,
            $identityProvider,
            $translationHelper,
            $dateHelper,
            $navigation
        );
    }
}
