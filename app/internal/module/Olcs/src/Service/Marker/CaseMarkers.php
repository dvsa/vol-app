<?php

namespace Olcs\Service\Marker;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;
use Olcs\Service\Marker\Markers;

/**
 * Class CaseMarkers service. Used to contain business logic for generating markers
 * @package Olcs\Service
 */
class CaseMarkers extends Markers
{
    /**
     * Generate marker types based on array of types and data
     *
     * @param array $markerTypes
     * @param array $data
     * @return array
     */
    public function generateMarkerTypes($markerTypes, $data)
    {
        if (isset($data['case'])) {
            $this->setCase($data['case']);
        }

        if (is_array($markerTypes)) {
            foreach ($markerTypes as $type) {
                if (empty($this->getTypeMarkers($type)) &&
                    !empty($this->getCase())
                ) {
                    $generateMethod = 'generate' . ucfirst($type) . 'Markers';
                    $dataMethod = 'get' . ucfirst($type) . 'MarkerData';

                    if (method_exists($this, $dataMethod) && method_exists($this, $generateMethod)) {
                        $data = $this->$dataMethod();
                        $markers = $this->$generateMethod($data);
                        $this->setTypeMarkers($type, $markers);
                    }
                }
            }
        }
        return $this->getMarkers();
    }

    /**
     * Gets the data required to generate the stay marker. Extracted from case.
     *
     * @return array
     */
    private function getStayMarkerData()
    {
        $case = $this->getCase();
        return [
            'stayData' => $case['stays'],
            'appealData' => isset($case['appeals'][0]) ? $case['appeals'][0] : null,
        ];
    }

    /**
     * Generate the stay markers
     *
     * @param array $data
     * @return array
     */
    private function generateStayMarkers($data)
    {
        if (empty($data['appealData']) ||
            (!empty($data['appealData']['decisionDate']) &&
            !empty($data['appealData']['outcome']))
             ||
            !empty($data['appealData']['withdrawnDate'])
        ) {
            return [];
        }

        $markers = [];
        if (!empty($data['stayData']) && !empty($data['appealData'])) {
            for ($i=0; $i<count($data['stayData']); $i++) {
                $stay = $data['stayData'][$i];
                if (empty($stay['withdrawnDate'])) {
                    $markers[$i]['content'] = $this->generateStayMarkerContent($stay);
                }
            }
        }

        return $markers;
    }

    /**
     * Generates outcome status text
     * @param $outcome
     * @return string
     */
    private function generateStayMarkerContent($stay)
    {
        $content = 'Stay ';
        $content .= isset($stay['outcome']['id']) ?
            strtolower($stay['outcome']['description']) .  " pending appeal - \n" : " in progress - \n";
        $content .= $stay['stayType']['id'] == 'stay_t_ut' ?  ' UT ' : ' TC/TR ';
        $requestDate = new \DateTime($stay['requestDate']);
        $content .= $requestDate->format('d-m-Y');

        return $content;
    }
}
