<?php

declare(strict_types=1);

namespace Olcs\Controller\Licence\Vehicle;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Permits\Data\Mapper\MapperManager;

class AddVehicleSearchControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AddVehicleSearchController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get(TableFactory::class);
        $mapperManager = $container->get(MapperManager::class);
        $flashMessengerHelper = $container->get('ControllerPluginManager')->get('FlashMessenger');
        return new AddVehicleSearchController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper);
    }
}
