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
     * Alter action form
     *
     * @param \Zend\Form\Form $form
     */
    public function alterActionForm(Form $form)
    {
        $form = parent::alterActionForm($form);

        $filter = $form->getInputFilter();

        $hasVehicleElement = $filter->get('data')->has('noOfVehiclesRequired');
        $hasTrailerElement = $filter->get('data')->has('noOfTrailersRequired');

        if ($hasVehicleElement || $hasTrailerElement) {
            $data = $this->getEntityService()->getVehicleAuths(
                $this->getController()->params('child_id')
            );
        }

        if ($hasVehicleElement) {
            $this->attachCantIncreaseValidator(
                $filter->get('data')->get('noOfVehiclesRequired'),
                'vehicles',
                $data['noOfVehiclesRequired']
            );
        }

        if ($hasTrailerElement) {
            $this->attachCantIncreaseValidator(
                $filter->get('data')->get('noOfTrailersRequired'),
                'trailers',
                $data['noOfTrailersRequired']
            );
        }

        $this->disableAddressFields($form);

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
        $data = parent::alterFormDataOnPost($mode, $data, $childId);

        if ($mode === 'edit') {
            // this repopulates the address data in locked/disabled fields
            $addressData = $this->getAddressData($childId);
            $data['address'] = $addressData['operatingCentre']['address'];
        }
        return $data;
    }

    /**
     * Get total authorisations for licence
     *
     * @param int $id
     * @return array
     */
    protected function getTotalAuthorisationsForLicence($id)
    {
        return $this->getLvaEntityService()->getTotalAuths($id);
    }

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

        $data = $this->getTotalAuthorisationsForLicence($this->getIdentifier());

        $filter = $form->getInputFilter();

        foreach (['vehicles', 'trailers'] as $which) {
            $key = 'totAuth' . ucfirst($which);

            if ($filter->get('data')->has($key)) {
                $this->attachCantIncreaseValidator(
                    $filter->get('data')->get($key),
                    'total-' . $which,
                    $data[$key]
                );
            }
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

    /**
     * Add variation info message
     */
    public function addMessages($id)
    {
        return $this->getServiceLocator()->get('Lva\Variation')->addVariationMessage($id);
    }

    /**
     * Attach a can't increase validator
     *
     * @param Input $input
     * @param string $messageSuffix
     * @param int $previousValue
     */
    protected function attachCantIncreaseValidator($input, $messageSuffix, $previousValue)
    {
        $validatorChain = $input->getValidatorChain();

        $cantIncreaseValidator = $this->getServiceLocator()->get('CantIncreaseValidator');

        $licenceId = $this->getLicenceAdapter()->getIdentifier();

        $link = $this->getController()->url()->fromRoute('lva-licence/variation', ['licence' => $licenceId]);

        $message = $this->getServiceLocator()->get('Helper\Translation')
            ->translateReplace('cant-increase-' . $messageSuffix, [$link]);

        $cantIncreaseValidator->setGenericMessage($message);
        $cantIncreaseValidator->setPreviousValue($previousValue);

        $validatorChain->attach($cantIncreaseValidator);
    }
}
