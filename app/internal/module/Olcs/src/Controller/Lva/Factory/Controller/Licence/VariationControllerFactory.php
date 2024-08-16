<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Licence\VariationController;
use Olcs\Service\Processing\CreateVariationProcessingService;
use LmcRbacMvc\Service\AuthorizationService;

class VariationControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return VariationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $processingCreateVariation = $container->get(CreateVariationProcessingService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $navigation = $container->get('navigation');

        return new VariationController(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $processingCreateVariation,
            $formHelper,
            $flashMessengerHelper,
            $navigation
        );
    }
}
