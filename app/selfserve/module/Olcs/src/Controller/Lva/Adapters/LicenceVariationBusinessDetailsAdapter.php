<?php

/**
 * External Licence & Variation Business Details Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Interfaces\BusinessDetailsAdapterInterface;
use Common\Controller\Lva\Adapters\AbstractAdapter;

/**
 * External Licence & Variation Business Details Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceVariationBusinessDetailsAdapter extends AbstractAdapter implements BusinessDetailsAdapterInterface
{
    public function alterFormForOrganisation(Form $form, $orgId)
    {
        return $this->getServiceLocator()->get('Lva\BusinessDetails')->lockDetails($form);
    }

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
        return $this->getServiceLocator()->get('Lva\BusinessDetails')
            ->createChangeTask($data);
    }

    public function postCrudSave($action, $data)
    {
        return $this->getServiceLocator()->get('Lva\BusinessDetails')
            ->createSubsidiaryChangeTask($action, $data);
    }
}
