<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Licence\VariationController;
use Olcs\Service\Processing\CreateVariationProcessingService;
use LmcRbacMvc\Service\AuthorizationService;

class VariationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return VariationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $processingCreateVariation = $container->get(CreateVariationProcessingService::class);
        $lvaAdapter = $container->get(LicenceLvaAdapter::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new VariationController(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $processingCreateVariation,
            $lvaAdapter,
            $flashMessengerHelper
        );
    }
}
