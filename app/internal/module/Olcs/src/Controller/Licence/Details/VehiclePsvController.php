<?php

/**
 * Vehicle Psv Controller
 *
 * Internal - Licence - Vehicle PSV section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits\VehicleSafety as VehicleSafetyTraits;

/**
 * Vehicle Psv Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclePsvController extends AbstractLicenceDetailsController
{
    use VehicleSafetyTraits\VehiclePsvSection,
        VehicleSafetyTraits\InternalGenericVehicleSection,
        VehicleSafetyTraits\LicenceGenericVehicleSection;

    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_vehicle-safety_vehicle-psv';

    /**
     * Holds the service
     *
     * @var string
     */
    protected $service = 'Licence';

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'vehicle_psv';

    /**
     * Holds the data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version'
        ),
        'children' => array(
            'licenceVehicles' => array(
                'properties' => array(
                    'id',
                    'receivedDate',
                    'specifiedDate',
                    'deletedDate'
                ),
                'children' => array(
                    'vehicle' => array(
                        'properties' => array(
                            'vrm',
                            'isNovelty',
                        ),
                        'children' => array(
                            'psvType' => array(
                                'properties' => array('id')
                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * Remove the trailer fields for PSV
     *
     * @param \Zend\Form\Fieldset $form
     * @return \Zend\Form\Fieldset
     */
    protected function alterForm($form)
    {
        return $this->doAlterForm(parent::alterForm($form));
    }

    /**
     * Return the form table data
     *
     * @return array
     */
    protected function getFormTableData($id, $table)
    {
        $data = array('licence' => $this->load($id));

        return $this->formatTableData($data, $table);
    }

    /**
     * Save the vehicle
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $parts = explode('-', $this->getActionName());

        $action = array_pop($parts);

        return $this->internalActionSave($data, $action);
    }
}
