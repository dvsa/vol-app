<?php

/**
 * External Licence Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\LicenceOperatingCentreAdapter as CommonLicenceOperatingCentreAdapter;

/**
 * Licence Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentreAdapter extends CommonLicenceOperatingCentreAdapter
{
    /**
     * Alter the form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    public function alterForm(Form $form)
    {
        $form = parent::alterForm($form);

        if ($form->get('data')->has('totCommunityLicences')) {
            $formHelper = $this->getServiceLocator()->get('Helper\Form');

            $formHelper->lockElement(
                $form->get('data')->get('totCommunityLicences'),
                'community-licence-changes-contact-office'
            );
        }

        return $form;
    }

    /**
     * Alter action form
     *
     * @param \Zend\Form\Form $form
     */
    public function alterActionForm(Form $form)
    {
        $form = parent::alterActionForm($form);

        $this->disableAddressFields($form);

        $addressElement = $form->get('address');

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $lockedElements = array(
            $addressElement->get('addressLine1'),
            $addressElement->get('town'),
            $addressElement->get('postcode'),
            $addressElement->get('countryCode'),
        );

        foreach ($lockedElements as $element) {
            $formHelper->lockElement($element, 'operating-centre-address-requires-variation');
        }

        return $form;
    }

    /**
     * Process address lookup for main form
     *
     * @param Form $form
     * @param Request $request
     * @return type
     */
    public function processAddressLookupForm($form, $request)
    {
        return false;
    }

    /**
     * @param string $mode
     * @param array $data POST data
     * @return array
     */
    public function alterFormDataOnPost($mode, $data, $childId)
    {
        if ($mode === 'edit') {
            // this repopulates the address data in locked/disabled fields
            $addressData = $this->getAddressData($this->getController()->params('child_id'));
            $data['address'] = $addressData['operatingCentre']['address'];
        }
        return $data;
    }

    /**
     * Remove the advertisements fieldset and the confirmation checkboxes
     *
     * @param \Zend\Form\Form $form
     */
    protected function alterActionFormForGoods(Form $form)
    {
        parent::alterActionFormForGoods($form);

        $this->getServiceLocator()->get('Helper\Form')
            ->remove($form, 'advertisements')
            ->remove($form, 'data->sufficientParking')
            ->remove($form, 'data->permission');
    }

    /**
     * Alter the form with all the traffic area stuff
     *
     * @param \Zend\Form\Form $form
     */
    protected function alterFormForTrafficArea(Form $form)
    {
        // Do nothing externally
    }

    /**
     * Format crud data for save
     *
     * @param array $data
     */
    protected function formatCrudDataForSave($data)
    {
        $data = parent::formatCrudDataForSave($data);

        unset($data['operatingCentre']['addresses']);

        return $data;
    }
}
