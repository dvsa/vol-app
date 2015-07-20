<?php

/**
 * Licence Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres as CommonLicenceOperatingCentres;
use Zend\Form\Form;

/**
 * Licence Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentres extends CommonLicenceOperatingCentres
{
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);

        if ($form->get('data')->has('totCommunityLicences')) {
            $this->getFormHelper()->disableElement($form, 'data->totCommunityLicences');
            $this->getFormHelper()->lockElement(
                $form->get('data')->get('totCommunityLicences'),
                'community-licence-changes-contact-office'
            );
        }
    }
}
