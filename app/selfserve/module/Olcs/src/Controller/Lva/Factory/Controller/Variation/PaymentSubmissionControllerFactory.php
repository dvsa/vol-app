<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\PaymentSubmissionController;
use ZfcRbac\Service\AuthorizationService;

class PaymentSubmissionControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PaymentSubmissionController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PaymentSubmissionController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelper = $container->get(FormHelperService::class);

        return new PaymentSubmissionController(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $flashMessengerHelper,
            $tableFactory,
            $formHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PaymentSubmissionController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): PaymentSubmissionController
    {
        return $this->__invoke($serviceLocator, PaymentSubmissionController::class);
    }
}
