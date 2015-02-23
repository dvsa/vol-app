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
    public function addMessages($orgType)
    {
        // no guidance on variations for soles / partnerships
        if ($this->isExceptionalType($orgType)) {
            return;
        }

        return $this->getServiceLocator()
            ->get('Lva\LicencePeople')
            ->addVariationMessage($this->getController());
    }

    public function alterFormForOrganisation(Form $form, $table, $orgId, $orgType)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId, $orgType)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, $orgType);
    }

    public function canModify($orgId)
    {
        return false;
    }
}
