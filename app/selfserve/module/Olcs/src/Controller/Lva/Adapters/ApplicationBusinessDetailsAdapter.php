<?php

/**
 * External Application Business Details Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Interfaces\BusinessDetailsAdapterInterface;
use Common\Controller\Lva\Adapters\AbstractAdapter;

/**
 * External Application Business Details Adapter
 *
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ApplicationBusinessDetailsAdapter extends AbstractAdapter
{
    public function alterFormForOrganisation(Form $form, $orgId)
    {
        // if we haven't got any in force licences, crack on...
        if (!$this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\BusinessDetails')->lockDetails($form);
    }

    // @NOTE: the rest of these methods are so far identical to those in the
    // LicenceVariation external adapter. But they're just thin wrappers around services,
    // so it's preferable to keep them separate rather than force us down an awkward
    // path of inheritance.

    public function hasChangedTradingNames($orgId, $tradingNames)
    {
        return $this->getServiceLocator()->get('Entity\Organisation')
            ->hasChangedTradingNames($orgId, $tradingNames);
    }

    public function hasChangedRegisteredAddress($orgId, $address)
    {
        return $this->getServiceLocator()->get('Entity\Organisation')
            ->hasChangedRegisteredAddress($orgId, $address);
    }

    public function hasChangedNatureOfBusiness($orgId, $natureOfBusiness)
    {
        return $this->getServiceLocator()->get('Entity\Organisation')
            ->hasChangedNatureOfBusiness($orgId, $natureOfBusiness);
    }

    public function hasChangedSubsidiaryCompany($id, $data)
    {
        return $this->getServiceLocator()->get('Entity\Organisation')
            ->hasChangedSubsidiaryCompany($id, $data);
    }

    public function postSave($data)
    {
        $this->getServiceLocator()->get('Lva\BusinessDetails')
            ->createChangeTask($data);
    }

    public function postCrudSave($action, $data)
    {
        $this->getServiceLocator()->get('Lva\BusinessDetails')
            ->createSubsidiaryChangeTask($action, $data);
    }
}
