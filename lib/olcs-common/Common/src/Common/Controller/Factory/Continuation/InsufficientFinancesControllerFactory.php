<?php

namespace Common\Controller\Factory\Continuation;

use Common\Controller\Continuation\InsufficientFinancesController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

class InsufficientFinancesControllerFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InsufficientFinancesController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);
        return new InsufficientFinancesController(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
            $translationHelper,
            $formHelper,
            $uploadHelper,
            $guidanceHelper
        );
    }
}
