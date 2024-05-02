<?php

namespace Olcs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Class RenderMarkers
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class RenderMarkers extends AbstractHelper
{
    private $markerService;

    /**
     * @return \Olcs\Service\Marker\MarkerService
     */
    public function getMarkerService()
    {
        return $this->markerService;
    }

    /**
     * @param \Olcs\Service\Marker\MarkerService $markerService
     * @return \Olcs\View\Helper\Markers
     */
    public function setMarkerService($markerService)
    {
        $this->markerService = $markerService;
        return $this;
    }

    /**
     * Render markers.
     *
     * @param array $showMarkers Marker class names to show, if empty will show all available Markers
     *
     * @return string
     */
    public function __invoke(array $showMarkers = [])
    {
        $markers = $this->getMarkerService()->getMarkers();
        if (empty($markers)) {
            return false;
        }
        $html = '';
        $html .= '<div class="notice-container">';
        foreach ($markers as $marker) {
            if (empty($showMarkers) || in_array($marker::class, $showMarkers)) {
                $html .= $marker->render();
            }
        }
        $html .= '</div>';

        return $html;
    }
}
