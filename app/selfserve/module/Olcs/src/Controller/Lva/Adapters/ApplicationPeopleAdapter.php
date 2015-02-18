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
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationPeopleAdapter extends AbstractAdapter
{

    public function alterFormForOrganisation(Form $form, $table, $orgId)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        $orgData = $this->getServiceLocator()
            ->get('Entity\Organisation')
            ->getType($orgId);

        switch ($orgData['type']['id']) {
            case OrganisationEntityService::ORG_TYPE_PARTNERSHIP:
                return $this->getServiceLocator()->get('Lva\People')->lockPartnershipForm($form, $table);
        }
    }

    public function alterSoleTraderFormForOrganisation(Form $form, $orgId)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId)
    {
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, true);
    }

    public function canModify($orgId)
    {
        return !$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId);
    }
}
