<?php

/**
 * Variation Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\VariationOperatingCentres as CommonVariationOperatingCentres;
use Zend\Form\Form;

/**
 * Variation Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentres extends CommonVariationOperatingCentres
{
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);

        $table = $form->get('table')->get('table')->getTable();
        $table->removeColumn('noOfComplaints');

        if ($form->get('data')->has('totCommunityLicences')) {
            $this->getFormHelper()->lockElement(
                $form->get('data')->get('totCommunityLicences'),
                'community-licence-changes-contact-office'
            );
        }

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }
    }
}
