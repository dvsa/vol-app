<?php

/**
 * External Variation People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * External Variation People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VariationPeopleAdapter extends AbstractPeopleAdapter
{
    protected $tableConfig = 'lva-variation-people';

    public function alterFormForOrganisation(Form $form, $table, $orgId, $orgType)
    {
        // @TODO think about an abstract method to check org type is EITHER partnership or sole trader
        // (even here; sure, ST will never apply but the check doesn't hurt) as it's always used as
        // a behavioural switch when both false and true depending on the adapter
        if ($orgType === OrganisationEntityService::ORG_TYPE_PARTNERSHIP) {
            return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table, $orgId);
        }

        // otherwise go ahead with variation logic @TODO
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId, $orgType)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, $orgType);
    }

    public function canModify($orgId)
    {
        return false;
    }

    public function attachMainScripts()
    {
        $this->getServiceLocator()->get('Script')->loadFile('lva-crud-delta');
    }
}
