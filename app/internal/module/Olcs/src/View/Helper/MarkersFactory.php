<?php

namespace Olcs\View\Helper;

use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class MarkersFactory
 * @package Olcs\View\Helper
 */
class MarkersFactory implements \Laminas\ServiceManager\FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $markersHelper = new RenderMarkers();
        $markersHelper->setMarkerService(
            $serviceLocator->getServiceLocator()->get(\Olcs\Service\Marker\MarkerService::class)
        );
        return $markersHelper;
    }
}
