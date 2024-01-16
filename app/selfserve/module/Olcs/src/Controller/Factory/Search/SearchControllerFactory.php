<?php

namespace Olcs\Controller\Factory\Search;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Search\SearchController;
use LmcRbacMvc\Service\AuthorizationService;

class SearchControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SearchController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $navigation = $container->get('Navigation');
        $formElementManager = $container->get('FormElementManager');
        $viewHelperManager = $container->get('ViewHelperManager');
        $dataServiceManager = $container->get('DataServiceManager');
        $translationHelper = $container->get(TranslationHelperService::class);

        return new SearchController(
            $niTextTranslationUtil,
            $authService,
            $scriptFactory,
            $formHelper,
            $navigation,
            $formElementManager,
            $viewHelperManager,
            $dataServiceManager,
            $translationHelper
        );
    }
}
