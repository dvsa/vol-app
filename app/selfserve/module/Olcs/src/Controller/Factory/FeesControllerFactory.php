<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\FeesController;
use LmcRbacMvc\Service\AuthorizationService;

class FeesControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return FeesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FeesController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $tableFactory = $container->get(TableFactory::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);

        return new FeesController(
            $niTextTranslationUtil,
            $authService,
            $tableFactory,
            $guidanceHelper,
            $scriptFactory,
            $formHelper,
            $urlHelper,
            $translationHelper
        );
    }
}
