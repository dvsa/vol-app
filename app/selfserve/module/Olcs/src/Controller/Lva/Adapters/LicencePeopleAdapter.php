<?php

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractControllerAwareAdapter;
use Common\Service\Entity\OrganisationEntityService;
use Common\Controller\Lva\Interfaces\PeopleAdapterInterface;

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapter extends AbstractControllerAwareAdapter implements PeopleAdapterInterface
{
    public function addMessages($orgId)
    {
        return $this->getServiceLocator()->get('Lva\LicencePeople')->maybeAddVariationMessage(
            $this->getController(),
            $orgId
        );
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
