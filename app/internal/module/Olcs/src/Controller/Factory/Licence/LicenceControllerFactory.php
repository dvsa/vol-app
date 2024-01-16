<?php

namespace Olcs\Controller\Factory\Licence;

use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Licence\LicenceController;

class LicenceControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ApplicationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $oppositionHelper = $container->get(OppositionHelperService::class);
        $complaintsHelper = $container->get(ComplaintsHelperService::class);
        $navigation = $container->get('Navigation');
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new LicenceController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $oppositionHelper,
            $complaintsHelper,
            $navigation,
            $flashMessengerHelper
        );
    }
}
