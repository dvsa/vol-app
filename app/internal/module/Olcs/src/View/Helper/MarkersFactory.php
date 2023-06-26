<?php

namespace Olcs\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Service\Marker\MarkerService;

/**
 * Class MarkersFactory
 * @package Olcs\View\Helper
 */
class MarkersFactory implements \Laminas\ServiceManager\FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return RenderMarkers
     */
    public function createService(ServiceLocatorInterface $serviceLocator): RenderMarkers
    {
        return $this->__invoke($serviceLocator, RenderMarkers::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RenderMarkers
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RenderMarkers
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $markersHelper = new RenderMarkers();
        $markersHelper->setMarkerService(
            $container->get(MarkerService::class)
        );
        return $markersHelper;
    }
}
