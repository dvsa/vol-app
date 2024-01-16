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
use Olcs\Controller\Lva\Variation\InterimController;
use LmcRbacMvc\Service\AuthorizationService;

class InterimControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return InterimController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InterimController
    {
        
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $tableFactory = $container->get(TableFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $navigation = $container->get('Navigation');

        return new InterimController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formHelper,
            $scriptFactory,
            $tableFactory,
            $stringHelper,
            $formServiceManager,
            $navigation
        );
    }
}
