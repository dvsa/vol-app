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
        $markers = [];
        if (!empty($data['busRegData'])) {
            if (!empty($data['busRegData']['isTxcApp'])
                && ($data['busRegData']['isTxcApp'] === 'Y')
            ) {
                // EBSR marker
                array_push(
                    $markers,
                    [
                        'content' => $this->generateEbsrMarkerContent($data['busRegData']['ebsrRefresh']),
                        'data' => $this->generateEbsrMarkerData()
                    ]
                );
            }

            if (!empty($data['busRegData']['shortNoticeRefused'])
                && ($data['busRegData']['shortNoticeRefused'] === 'Y')
            ) {
                // Short Notice Refused marker
                array_push(
                    $markers,
                    [
                        'content' => $this->generateShortNoticeRefusedMarkerContent(),
                        'data' => $this->generateShortNoticeRefusedMarkerData()
                    ]
                );
            }
        }

        return $markers;
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

    /**
     * Generates EBSR marker content
     *
     * @param bool $ebsrRefresh
     * @return string
     */
    protected function generateEbsrMarkerContent($ebsrRefresh)
    {
        $content = 'Submitted by EBSR';

        if ($ebsrRefresh === 'Y') {
            $content .= ' data refresh';
        }

        return $content;
    }

    /**
     * Generates data associated with the content for the marker.
     *
     * @return array
     */
    protected function generateEbsrMarkerData()
    {
        return [];
    }
}
