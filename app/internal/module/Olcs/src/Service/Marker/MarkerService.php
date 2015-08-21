<?php

namespace Olcs\Service\Marker;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\FactoryInterface;

/**
 * MarkerService
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkerService implements FactoryInterface
{
    /**
     * @var MarkerPluginManager
     */
    private $markerPluginManager;

    /**
     * Array of data to populate markers
     *
     * @var array
     */
    private $data = [];

    /**
     * @return MarkerPluginManager
     */
    public function getMarkerPluginManager()
    {
        return $this->markerPluginManager;
    }

    /**
     * @param MarkerPluginManager $markerPluginManager
     *
     * @return MarkerService
     */
    public function setMarkerPluginManager(\Olcs\Service\Marker\MarkerPluginManager $markerPluginManager)
    {
        $this->markerPluginManager = $markerPluginManager;
        return $this;
    }

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return MarkerService
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $this->setMarkerPluginManager($serviceLocator->get(\Olcs\Service\Marker\MarkerPluginManager::class));

        return $this;
    }

    /**
     * Add data to populate markers
     *
     * @param string $key  A key eg "licence", "application"
     * @param mixed  $data
     */
    public function addData($key, $data)
    {
        $this->data[$key] = $data;
    }

    /**
     * Get a list of markers that can be displayed
     *
     * @return array
     */
    public function getMarkers()
    {
        $markers = [];

        /* @var $marker MarkerInterface */
        $markerServices = $this->getMarkerPluginManager()->getRegisteredServices();
        foreach ($markerServices['invokableClasses'] as $markerName) {
            $marker = $this->getMarkerPluginManager()->get($markerName);

            // set the data we have into the marker, it can then workout whether it can render or not
            $marker->setData($this->data);

            if ($marker->canRender()) {
                $markers[] = $marker;
            }
        }

        return $markers;
    }
}
