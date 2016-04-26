<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\AbstractOperatingCentres;
use Common\RefData;
use Zend\Form\Form;

/**
 * Variation Operating Centres
 */
class VariationOperatingCentres extends AbstractOperatingCentres
{
    /**
     * @see AbstractOperatingCentres::allowChangingTrafficArea
     */
    protected function allowChangingTrafficArea($trafficAreaId)
    {
        // Traffic area can be changed as long as its not Northern Irelend
        return ($trafficAreaId !== RefData::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
    }

    /**
     * @see AbstractOperatingCentres::removeTrafficAreaElements
     */
    protected function removeTrafficAreaElements($data)
    {
        return false;
    }
}
