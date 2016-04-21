<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\AbstractOperatingCentres;
use Zend\Form\Form;

/**
 * Variation Operating Centres
 */
class VariationOperatingCentres extends AbstractOperatingCentres
{
    protected function allowChangingTrafficArea($trafficAreaId)
    {
        // Traffic area can be changed as long as its not Northern Irelend
        return ($trafficAreaId !== 'N');
    }
}
