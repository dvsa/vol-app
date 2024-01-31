<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\TypeOfLicenceController;
use LmcRbacMvc\Service\AuthorizationService;

class TypeOfLicenceControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TypeOfLicenceController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TypeOfLicenceController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $stringHelper = $container->get(StringHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $navigation = $container->get('navigation');

        return new TypeOfLicenceController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $scriptFactory,
            $formServiceManager,
            $stringHelper,
            $restrictionHelper,
            $formHelper,
            $navigation
        );
    }
}
