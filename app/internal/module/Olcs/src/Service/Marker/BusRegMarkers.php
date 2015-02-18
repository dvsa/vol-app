<?php

namespace Olcs\Service\Marker;

use Olcs\Service\Marker\Markers;

/**
 * Class BusRegMarkers service. Used to contain business logic for generating markers
 * @package Olcs\Service
 */
class BusRegMarkers extends Markers
{
    /**
     * Gets the data required to generate the BusReg marker.
     *
     * @return array
     */
    protected function getBusRegMarkerData()
    {
        return [
            'busRegData' => $this->getBusReg(),
        ];
    }

    /**
     * Generate the BusReg markers
     *
     * @param array $data
     * @return array
     */
    protected function generateBusRegMarkers($data)
    {
        $marker = [];
        if (!empty($data['busRegData'])) {
            if (!empty($data['busRegData']['shortNoticeRefused'])
                && ($data['busRegData']['shortNoticeRefused'] === 'Y')
            ) {
                array_push(
                    $marker,
                    [
                        'content' => $this->generateShortNoticeRefusedMarkerContent(),
                        'data' => $this->generateShortNoticeRefusedMarkerData()
                    ]
                );
            }
        }

        return $marker;
    }

    /**
     * Generates Short Notice Refused marker content
     *
     * @return string
     */
    protected function generateShortNoticeRefusedMarkerContent()
    {
        $content = 'Refused by short notice. Short notice is disabled.';

        return $content;
    }

    /**
     * Generates data associated with the content for the marker.
     *
     * @return array
     */
    protected function generateShortNoticeRefusedMarkerData()
    {
        return [];
    }
}
