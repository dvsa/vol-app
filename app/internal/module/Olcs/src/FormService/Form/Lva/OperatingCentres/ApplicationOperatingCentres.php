<?php

/**
 * Application Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\AbstractOperatingCentres;
use Common\RefData;
use Zend\Form\Form;

/**
 * Application Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationOperatingCentres extends AbstractOperatingCentres
{
    protected function alterForm(Form $form, array $params)
    {
        $this->getFormServiceLocator()->get('lva-application')->alterForm($form);

        parent::alterForm($form, $params);
    }

    protected function allowChangingTrafficArea($trafficAreaId)
    {
        // Traffic area can be changed as long as its not Northern Irelend
        return ($trafficAreaId !== RefData::NORTHERN_IRELAND_TRAFFIC_AREA_CODE);
    }
}
