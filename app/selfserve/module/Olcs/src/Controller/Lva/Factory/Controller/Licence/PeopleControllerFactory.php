<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Adapters\LicencePeopleAdapter;
use Olcs\Controller\Lva\Licence\PeopleController;
use LmcRbacMvc\Service\AuthorizationService;

class PeopleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
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
        $translationHelper = $container->get(TranslationHelperService::class);
        $lvaAdapter = $container->get(LicencePeopleAdapter::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new PeopleController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $variationLvaService,
            $guidanceHelper,
            $translationHelper,
            $lvaAdapter,
            $flashMessengerHelper
        );
    }
}
