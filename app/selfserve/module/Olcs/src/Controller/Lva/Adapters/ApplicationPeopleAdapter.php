<?php

/**
 * External Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;

/**
 * External Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapter extends VariationPeopleAdapter
{
    public function alterFormForOrganisation(Form $form, $table)
    {
        if ($this->canModify()) {
            parent::alterFormForOrganisation($form, $table);
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table);
    }

    public function alterAddOrEditFormForOrganisation(Form $form)
    {
        if ($this->canModify()) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, $this->getOrganisationType());
    }

    public function canModify()
    {
        if ($this->isOrganisationLimited()) {
            return true;
        }

        return !$this->hasInforceLicences();
    }
}
