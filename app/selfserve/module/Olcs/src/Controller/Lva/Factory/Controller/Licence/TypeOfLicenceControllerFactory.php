<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Licence\TypeOfLicenceController;
use LmcRbacMvc\Service\AuthorizationService;

class TypeOfLicenceControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TypeOfLicenceController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TypeOfLicenceController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $variationLvaService = $container->get(VariationLvaService::class);
        $lvaAdapter = $container->get(LicenceLvaAdapter::class);

        return new TypeOfLicenceController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $scriptFactory,
            $formServiceManager,
            $variationLvaService,
            $lvaAdapter
        );
    }
}
