<?php

/**
 * External Variation Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\VariationOperatingCentreAdapter as CommonVariationOperatingCentreAdapter;

/**
 * Variation Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentreAdapter extends CommonVariationOperatingCentreAdapter
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
     * Extend the abstract behaviour to alter the action form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    public function alterActionForm(Form $form)
    {
        $form = parent::alterActionForm($form);

        $action = $this->getOperatingCentreAction();

        if ($action !== self::ACTION_ADDED) {
            $this->disableAddressFields($form);
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
        $action = $this->getOperatingCentreAction();

        if ($action !== self::ACTION_ADDED) {
            return false;
        }

        return parent::processAddressLookupForm($form, $request);
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
}
