<?php

namespace Olcs\Service\Marker;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;
use Olcs\Service\Marker\CaseMarkers;

/**
 * Class LicenceMarkers service. Used to contain business logic for generating markers
 * @package Olcs\Service
 */
class LicenceMarkers extends CaseMarkers
{
    /**
     * Generates stay marker content
     *
     * @param $stay
     * @return string
     */
    protected function generateStayMarkerContent($stay)
    {
        $content = "Case %s \nStay ";
        $content .= isset($stay['outcome']['id']) ?
            strtolower($stay['outcome']['description']) .  " pending appeal \n" : " in progress \n";
        $content .= $stay['stayType']['id'] == 'stay_t_ut' ?  ' UT ' : ' TC/TR ';
        $requestDate = new \DateTime($stay['requestDate']);
        $content .= $requestDate->format('d/m/Y');

        return $content;
    }

    /**
     * Generates data associated with the content for the marker. For licences, we require a link to be generated
     *
     * @param array $stay
     * @return array
     */
    protected function generateStayMarkerData()
    {
        $data[] = [
            'type' => 'url',
            'route' => 'case_hearing_appeal',
            'params' => ['case' => $this->getCase()['id']],
            'linkText' => $this->getCase()['id']
        ];

        return $data;
    }

    /**
     * Generates appeal marker content
     * @param array $appeal
     * @return string
     */
    protected function generateAppealMarkerContent($appeal)
    {
        $content = "Case %s \nAppeal in progress \n";
        $appealDate = new \DateTime($appeal['appealDate']);
        $content .= $appealDate->format('d/m/Y');

        return $content;
    }

    /**
     * Generates data associated with the content for the marker. For licences, we require a link to be generated
     *
     * @return array
     */
    protected function generateAppealMarkerData()
    {
        return $this->generateStayMarkerData();
    }
}
