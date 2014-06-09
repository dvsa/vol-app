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
     * Holds the table data bundle
     *
     * @var array
     */
    protected $tableDataBundle = array(
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
     * Add operating centre
     */
    public function addAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit operating centre
     */
    public function editAction()
    {
        return $this->renderSection();
    }

    /**
     * Performs delete action
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function deleteAction()
    {
        $vehicleId = $this->getActionId();

        $licence = $this->getLicenceData();

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
     * Placeholder for save
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {
    }

    /**
     * Get table data
     *
     * @param int $id
     * @return array
     */
    protected function getTableData($id)
    {
        unset($id);

        $licence = $this->getLicenceData();

        $data = $this->makeRestCall('Licence', 'GET', array('id' => $licence['id']), $this->tableDataBundle);

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
     * We don't need to load anything as there is no form
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        return array();
    }

    /**
     * Save the vehicle
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $this->saveVehicle($data, $this->getActionName());
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
