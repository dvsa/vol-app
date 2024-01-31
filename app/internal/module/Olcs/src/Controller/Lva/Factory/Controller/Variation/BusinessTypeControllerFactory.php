<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\Controller\Lva\Adapters\GenericBusinessTypeAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\BusinessTypeController;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use LmcRbacMvc\Service\AuthorizationService;

class BusinessTypeControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return BusinessTypeController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusinessTypeController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $queryService = $container->get(QueryService::class);
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $lvaAdapter = $container->get(GenericBusinessTypeAdapter::class);
        $navigation = $container->get('navigation');

        return new BusinessTypeController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $identityProvider,
            $translationHelper,
            $transferAnnotationBuilder,
            $queryService,
            $stringHelper,
            $lvaAdapter,
            $navigation
        );
    }
}
