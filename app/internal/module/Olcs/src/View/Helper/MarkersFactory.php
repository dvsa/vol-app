<?php

namespace Olcs\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MarkersFactory
 * @package Olcs\View\Helper
 */
class MarkersFactory implements \Zend\ServiceManager\FactoryInterface
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
