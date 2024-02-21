<?php

namespace Olcs\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Marker\MarkerService;

class MarkersFactory implements FactoryInterface
{
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
        $markersHelper = new RenderMarkers();
        $markersHelper->setMarkerService(
            $container->get(MarkerService::class)
        );
        return $markersHelper;
    }
}
