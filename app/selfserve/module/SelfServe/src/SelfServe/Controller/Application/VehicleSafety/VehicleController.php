<?php

/**
 * Vehicle Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\VehicleSafety;

/**
 * Vehicle Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleController extends VehicleSafetyController
{
    /**
     * Action service
     *
     * @var string
     */
    protected $actionService = 'Vehicle';

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            ),
            'values' => array(
                'bodyType' => 'vhl_body_type.flat',
                'isTipper' => 0,
                'isRefrigerated' => 0,
                'isArticulated' => 0,
                'certificateNumber' => '',
                'viAction' => ''
            )
        )
    );

    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Placeholder for save
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {

    }

    /**
     * Performs delete action
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function deleteAction()
    {
        $vehicleId = $this->getActionId();

        $licence = $this->getLicenceData(array('id'));

        $cond = array(
            'vehicle' => $vehicleId,
            'licence' => $licence['id'],
        );

        $bundle = array(
            'properties' => array(
                'id'
            )
        );

        $licenceVehicle = $this->makeRestCall('LicenceVehicle', 'GET', $cond, $bundle);

        if (empty($licenceVehicle) || (isset($licenceVehicle['Count']) && $licenceVehicle['Count'] == 0)) {
            return $this->notFoundAction();
        }

        $this->makeRestCall('LicenceVehicle', 'DELETE', array('id' => $licenceVehicle['Results'][0]['id']));

        return $this->goBackToSection();
    }

    /**
     * Get table data
     *
     * @param int $id
     * @return array
     */
    protected function getTableData($id)
    {
        $bundle = array(
            'properties' => null,
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => null,
                    'children' => array(
                        'vehicle' => array(
                            'properties' => array(
                                'id',
                                'vrm',
                                'platedWeight'
                            )
                        )
                    )
                )
            )
        );

        $licence = $this->getLicenceData(array('id'));

        $data = $this->makeRestCall('Licence', 'GET', array('id' => $licence['id']), $bundle);

        $results = array();

        if (isset($data['licenceVehicles']) && !empty($data['licenceVehicles'])) {

            foreach ($data['licenceVehicles'] as $licenceVehicle) {

                if (isset($licenceVehicle['vehicle']) && !empty($licenceVehicle['vehicle'])) {
                    $results[] = $licenceVehicle['vehicle'];
                }
            }
        }

        return $results;
    }

    /**
     * Save the vehicle
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $saved = parent::actionSave($data);

        if ($this->getActionName() == 'add') {

            if (!isset($saved['id'])) {

                return $this->notFoundAction();
            }

            $licence = $this->getLicenceData(array('id'));

            $licenceVehicleData = array(
                'licence' => $licence['id'],
                'dateApplicationReceived' => date('Y-m-d H:i:s'),
                'vehicle' => $saved['id']
            );

            parent::actionSave($licenceVehicleData, 'LicenceVehicle');
        }
    }

    /**
     * Format the data for the form
     *
     * @param array $data
     * @return array
     */
    protected function processActionLoad($data)
    {
        return array('data' => $data);
    }
}
