<?php

/**
 * External Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * External Application People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapter extends AbstractAdapter
{
    public function addMessages($orgId)
    {
    }

    public function alterFormForOrganisation(Form $form, $table, $orgId)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table, $orgId);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId, $orgType)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, $orgType);
    }

    public function canModify($orgId)
    {
        return !$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId);
    }
}
