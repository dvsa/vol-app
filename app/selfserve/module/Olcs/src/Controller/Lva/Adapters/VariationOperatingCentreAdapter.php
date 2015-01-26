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

    public function handleFees()
    {
        $applicationId = $this->getVariationAdapter()->getIdentifier();
        $licenceId = $this->getLicenceAdapter()->getIdentifier();

        $applicationOcs = $this->getServiceLocator()->get('Entity\ApplicationOperatingCentre')
            ->getForApplication($applicationId);

        $licenceOcs = $this->getServiceLocator()->get('Entity\LicenceOperatingCentre')
            ->getOperatingCentresForLicence($licenceId)['Results']; // @TODO make this consistent

        if ($this->feeApplies($applicationOcs, $licenceOcs)) {
            $this->getServiceLocator()->get('Processing\Application')
                ->maybeCreateVariationFee($applicationId, $licenceId);
        } else {
            $this->getServiceLocator()->get('Processing\Application')
                ->maybeCancelVariationFee($applicationId);
        }
    }

    /**
     * @return boolean
     */
    protected function feeApplies($applicationOcs, $licenceOcs)
    {
        if (count($applicationOcs) > count($licenceOcs)) {
            // operating centre added, fee applies
            return true;
        }

        foreach($applicationOcs as $aoc) {
            foreach ($licenceOcs as $loc) {
                if ($aoc['operatingCentre']['id'] == $loc['operatingCentre']['id']) {
                    if ($aoc['noOfVehiclesRequired'] > $loc['noOfVehiclesRequired']) {
                        // increased vehicle auth, fee applies
                        return true;
                    }
                    if ($aoc['noOfTrailersRequired'] > $loc['noOfTrailersRequired']) {
                        // increased trailer auth, fee applies
                        return true;
                    }
                }
            }
        }

        // no fee applies
        return false;
    }
}
