<?php

/**
 * External Licence Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\LicenceOperatingCentreAdapter as CommonLicenceOperatingCentreAdapter;

/**
 * Licence Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentreAdapter extends CommonLicenceOperatingCentreAdapter
{
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

        $helper = $this->getServiceLocator()->get('Helper\Form');

        $lockedElements = array(
            $addressElement->get('addressLine1'),
            $addressElement->get('town'),
            $addressElement->get('postcode'),
            $addressElement->get('countryCode'),
        );

        foreach ($lockedElements as $element) {
            $helper->lockElement($element, 'operating-centre-address-requires-variation');
        }

        return $form;
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
}
