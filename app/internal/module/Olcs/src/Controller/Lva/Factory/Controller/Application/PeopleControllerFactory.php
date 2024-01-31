<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\Controller\Lva\Adapters\ApplicationPeopleAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\PeopleController;
use LmcRbacMvc\Service\AuthorizationService;

class PeopleControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return PeopleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PeopleController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $variationLvaService = $container->get(VariationLvaService::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $lvaAdapter = $container->get(ApplicationPeopleAdapter::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $navigation = $container->get('navigation');

        return new PeopleController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $variationLvaService,
            $guidanceHelper,
            $stringHelper,
            $lvaAdapter,
            $restrictionHelper,
            $flashMessengerHelper,
            $navigation
        );
    }
}
