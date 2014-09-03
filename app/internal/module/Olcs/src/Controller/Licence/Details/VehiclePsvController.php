<?php

/**
 * VehiclePsv Controller
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits\VehiclePsvSection;

/**
 * VehiclePsv Controller
 */
class VehiclePsvController extends AbstractLicenceDetailsController
{
    use VehiclePsvSection;

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
        'properties' => null,
        'children' => array(
            'licenceVehicles' => array(
                'properties' => null,
                'children' => array(
                    'vehicle' => array(
                        'properties' => array(
                            'id',
                            'vrm',
                            'makeModel',
                            'isNovelty'
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
     * Return the form table data
     *
     * @return array
     */
    protected function getFormTableData($id, $table)
    {
        $data = $this->load($id);

        $data = array(
            'licence' => $data
        );

        return $this->formatTableData($data, $table);
    }
}
