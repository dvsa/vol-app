<?php

namespace Common\Controller\Factory\Continuation;

use Common\Controller\Continuation\PaymentController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

class PaymentControllerFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PaymentController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        return new PaymentController($niTextTranslationUtil, $authService, $formServiceManager, $translationHelper, $urlHelper, $tableFactory);
    }
}
