<?php

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapter extends AbstractPeopleAdapter
{
    public function addMessages($orgId, $id)
    {
        // no guidance on variations for soles / partnerships
        if ($this->isExceptionalOrganisation($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\Variation')->addVariationMessage($id);
    }

    public function alterFormForOrganisation(Form $form, $table, $orgId)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm(
            $form,
            $this->getOrganisationType($orgId)
        );
    }

    public function canModify($orgId)
    {
        return false;
    }
}
