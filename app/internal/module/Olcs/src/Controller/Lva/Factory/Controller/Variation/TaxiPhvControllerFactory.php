<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\TaxiPhvController;
use LmcRbacMvc\Service\AuthorizationService;

class TaxiPhvControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TaxiPhvController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TaxiPhvController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $navigation = $container->get('navigation');

        return new TaxiPhvController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $tableFactory,
            $scriptFactory,
            $translationHelper,
            $stringHelper,
            $navigation
        );
    }
}
