<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Application\Controller\PaymentSubmissionController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
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
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);

        return new PaymentSubmissionController(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $flashMessengerHelper,
            $tableFactory,
            $formHelper,
            $restrictionHelper,
            $stringHelper
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
