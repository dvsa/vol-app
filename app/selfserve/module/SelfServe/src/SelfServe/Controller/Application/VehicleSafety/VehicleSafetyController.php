<?php

/**
 * VehicleSafety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\VehicleSafety;

use SelfServe\Controller\Application\ApplicationController;

/**
 * VehicleSafety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleSafetyController extends ApplicationController
{
    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->goToFirstSubSection();
    }

    /**
     * Save vehicle
     *
     * @param array $data
     * @throws \Exception
     */
    protected function saveVehicle($data, $action)
    {
        $saved = parent::actionSave($data);

        if ($action == 'add') {

            if (!isset($saved['id'])) {

                throw new \Exception('Unable to save vehicle');
            }

            $licence = $this->getLicenceData();

            $licenceVehicleData = array(
                'licence' => $licence['id'],
                'dateApplicationReceived' => date('Y-m-d H:i:s'),
                'vehicle' => $saved['id']
            );

            parent::actionSave($licenceVehicleData, 'LicenceVehicle');
        }
    }
}
