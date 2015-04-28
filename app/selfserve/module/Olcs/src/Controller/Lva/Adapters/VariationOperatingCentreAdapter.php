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

        $table = $form->get('table')->get('table')->getTable();
        $table->removeColumn('noOfComplaints');

        if ($form->get('data')->has('totCommunityLicences')) {
            $formHelper = $this->getServiceLocator()->get('Helper\Form');

            $formHelper->lockElement(
                $form->get('data')->get('totCommunityLicences'),
                'community-licence-changes-contact-office'
            );
        }

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }

        return $form;
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
            ->getAuthorityDataForLicence($licenceId);

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

        foreach ($applicationOcs as $aoc) {

            switch ($aoc['action']) {
                case self::ACTION_ADDED:
                    // operating centre added, fee always applies
                    return true;
                case self::ACTION_UPDATED:
                    // if there's an increase in auth. at a centre, fee applies
                    if ($this->hasIncreasedAuth($aoc, $licenceOcs)) {
                        return true;
                    }
                    break;
            }
        }

        // no fee applies
        return false;
    }

    /**
     * Helper function to determine if an update has increased the vehicle or
     * trailer authorisation vs. the existing licence
     *
     * @return boolean
     */
    protected function hasIncreasedAuth($aoc, $licenceOcs)
    {
        foreach ($licenceOcs as $loc) {
            if ($aoc['operatingCentre']['id'] == $loc['operatingCentre']['id']) {
                if ($aoc['noOfVehiclesRequired'] > $loc['noOfVehiclesRequired']) {
                    // increased vehicle auth
                    return true;
                }
                if ($aoc['noOfTrailersRequired'] > $loc['noOfTrailersRequired']) {
                    // increased trailer auth
                    return true;
                }
            }
        }
        // no increase
        return false;
    }

    /**
     * On post, if there is an error, we don't re-fill in the address data as the fields are disabled
     * here we attach that data to the post data to prevent this problem
     *
     * @param string $mode
     * @param array $data
     */
    public function alterFormDataOnPost($mode, $data, $childId)
    {
        $data = parent::alterFormDataOnPost($mode, $data, $childId);

        if ($mode == 'edit') {
            // this repopulates the address data in locked/disabled fields
            $addressData = $this->getAddressData($childId);
            $data['address'] = $addressData['operatingCentre']['address'];
            $data['address']['countryCode'] = $data['address']['countryCode']['id'];
        }

        return $data;
    }
}
