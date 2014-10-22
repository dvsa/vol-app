<?php

namespace Olcs\Service\Marker;

use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class CaseMarkers service. Used to contain business logic for generating markers
 * @package Olcs\Service
 */
class CaseMarkers extends AbstractData
{

    public function getStayMarkers($data)
    {
        $markers['stay'] =
            [
                0 => [
                    'content' => 'Stay1 granted pending appeal'
                ]
            ];
        return $markers;
    }
}
