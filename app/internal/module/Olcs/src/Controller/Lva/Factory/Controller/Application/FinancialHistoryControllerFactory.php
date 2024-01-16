<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\FinancialHistoryController;
use LmcRbacMvc\Service\AuthorizationService;

class FinancialHistoryControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return FinancialHistoryController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FinancialHistoryController
    {
        
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $dataHelper = $container->get(DataHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $navigation = $container->get('Navigation');

        return new FinancialHistoryController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $dataHelper,
            $stringHelper,
            $uploadHelper,
            $restrictionHelper,
            $navigation
        );
    }
}
