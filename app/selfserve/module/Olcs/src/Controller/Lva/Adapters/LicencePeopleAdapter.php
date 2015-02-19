<?php

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapter extends AbstractAdapter
{
    public function alterFormForOrganisation(Form $form, $table, $orgId)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table, $orgId);
    }

    public function alterSoleTraderFormForOrganisation(Form $form, $orgId)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, true);
    }

    public function canModify($orgId)
    {
        return false;
    }
}
