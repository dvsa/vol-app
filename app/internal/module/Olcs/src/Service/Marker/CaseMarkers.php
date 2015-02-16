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
     * Gets the data required to generate the appeal marker. Extracted from case.
     *
     * @return array
     */
    protected function getAppealMarkerData()
    {
        return [
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
                    $markers[$i]['data'] = $this->generateStayMarkerData($stay);
                }
            }
        }

        return $markers;
    }

    /**
     * Generates data associated with the content for the marker. For cases, nothing needed.
     *
     * @param array $stay
     * @return array
     */
    protected function generateStayMarkerData()
    {
        return [];
    }

    /**
     * Generates data associated with the content for the marker. For cases, nothing needed.
     *
     * @param array $stay
     * @return array
     */
    protected function generateAppealMarkerData()
    {
        return [];
    }

    /**
     * Generate the appeal marker
     *
     * @param array $data
     * @return array
     */
    protected function generateAppealMarkers($data)
    {
        $marker = [];
        if (!empty($data['appealData'])) {
            if (empty($data['appealData']['withdrawnDate']) &&
                (empty($data['appealData']['decisionDate']) || empty($data['appealData']['outcome']))
            ) {
                array_push(
                    $marker,
                    [
                        'content' => $this->generateAppealMarkerContent($data['appealData']),
                        'data' => $this->generateAppealMarkerData()
                    ]
                );
            }
        }

        return $marker;
    }

    /**
     * Generates stay marker content
     *
     * @param $stay
     * @return string
     */
    protected function generateStayMarkerContent($stay)
    {
        $content = 'Stay ';
        $content .= isset($stay['outcome']['id']) ?
            strtolower($stay['outcome']['description']) .  " pending appeal - \n" : " in progress - \n";
        $content .= $stay['stayType']['id'] == 'stay_t_ut' ?  ' UT ' : ' TC/TR ';
        $requestDate = new \DateTime($stay['requestDate']);
        $content .= $requestDate->format('d/m/Y');

        return $content;
    }

    /**
     * Generates appeal marker content
     * @param array $appeal
     * @return string
     */
    protected function generateAppealMarkerContent($appeal)
    {
        $content = "Appeal in progress \n";
        $appealDate = new \DateTime($appeal['appealDate']);
        $content .= $appealDate->format('d/m/Y');

        return $content;
    }
}
