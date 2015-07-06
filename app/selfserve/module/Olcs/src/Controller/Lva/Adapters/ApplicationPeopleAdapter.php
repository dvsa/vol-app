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
        if (!$this->hasInforceLicences()) {
            return;
        }

        return parent::alterFormForOrganisation($form, $table);
    }

    public function alterAddOrEditFormForOrganisation(Form $form)
    {
        if (!$this->hasInforceLicences()) {
            return;
        }

        return parent::alterAddOrEditFormForOrganisation($form);
    }

    public function canModify()
    {
        if (!$this->hasInforceLicences()) {
            return true;
        }

        return parent::canModify();
    }
}
