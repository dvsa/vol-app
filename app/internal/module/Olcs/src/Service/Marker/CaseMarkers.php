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
     * Gets the data required to generate the stay marker. Extracted from case.
     *
     * @return array
     */
    protected function getStayMarkerData()
    {
        return [
            'stayData' => isset($this->getCase()['stays']) ? $this->getCase()['stays'] : [],
            'appealData' => isset($this->getCase()['appeals'][0]) ? $this->getCase()['appeals'][0] : [],
        ];
    }

    /**
     * Generate the stay markers
     *
     * @param array $data
     * @return array
     */
    protected function generateStayMarkers($data)
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
