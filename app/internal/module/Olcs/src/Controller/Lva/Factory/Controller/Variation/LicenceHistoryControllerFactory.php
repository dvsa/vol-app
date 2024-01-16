<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\LicenceHistoryController;
use LmcRbacMvc\Service\AuthorizationService;

class LicenceHistoryControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return LicenceHistoryController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceHistoryController
    {
        
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $navigation = $container->get('Navigation');

        return new LicenceHistoryController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $stringHelper,
            $tableFactory,
            $formHelper,
            $navigation
        );
    }
}
