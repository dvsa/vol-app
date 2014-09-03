<?php

/**
 * VehiclePsv Controller
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits;

/**
 * VehiclePsv Controller
 */
class VehiclePsvController extends AbstractLicenceDetailsController
{
    use Traits\VehiclePsvSection;

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
                'properties' => null,
                'children' => array(
                    'vehicle' => array(
                        'properties' => array(
                            'id',
                            'vrm',
                            'makeModel',
                            'isNovelty',
                            'specifiedDate',
                            'deletedDate'
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
     * We only want to show active vehicles
     *
     * @param array $vehicle
     * @return boolean
     */
    protected function showVehicle($vehicle)
    {
        if (empty($vehicle['specifiedDate'])) {
            return false;
        }

        return true;
    }
}
