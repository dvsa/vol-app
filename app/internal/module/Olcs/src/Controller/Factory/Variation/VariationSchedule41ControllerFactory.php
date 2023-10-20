<?php

namespace Olcs\Controller\Factory\Variation;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Variation\VariationSchedule41Controller;
use ZfcRbac\Service\AuthorizationService;

class VariationSchedule41ControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return VariationSchedule41Controller
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationSchedule41Controller
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);

        return new VariationSchedule41Controller(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $tableFactory,
            $flashMessengerHelper,
            $stringHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return VariationSchedule41Controller
     */
    public function createService(ServiceLocatorInterface $serviceLocator): VariationSchedule41Controller
    {
        return $this->__invoke($serviceLocator, VariationSchedule41Controller::class);
    }
}
