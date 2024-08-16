<?php

namespace Olcs\Service\Marker;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
     * @return MarkerService
     */
    public function setMarkerPluginManager(\Olcs\Service\Marker\MarkerPluginManager $markerPluginManager)
    {
        $this->markerPluginManager = $markerPluginManager;
        return $this;
    }

    /**
     * Add data to populate markers
     *
     * @param string $key  A key eg "licence", "application"
     */
    public function addData($key, mixed $data)
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
        $markerServices = $this->getMarkerPluginManager()->getMarkers();
        foreach ($markerServices as $markerName) {
            $marker = $this->getMarkerPluginManager()->get($markerName);

            // set the data we have into the marker, it can then workout whether it can render or not
            $marker->setData($this->data);

            if ($marker->canRender()) {
                $markers[] = $marker;
            }
        }

        return $markers;
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return MarkerService
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MarkerService
    {
        $this->setMarkerPluginManager($container->get(\Olcs\Service\Marker\MarkerPluginManager::class));
        return $this;
    }
}
