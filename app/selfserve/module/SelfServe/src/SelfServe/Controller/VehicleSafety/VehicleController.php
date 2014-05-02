<?php

/**
 * Vehicles Controller - responsible for CRUD vehicles
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\VehicleSafety;

/**
 * Vehicles Controller - responsible for CRUD vehicles
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class VehicleController extends AbstractVehicleSafetyController
{
    /**
     * Generates the next step form depending on which step the user is on.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->checkForCrudAction();

        $licence = $this->getLicenceEntity();
        $vehicleTable = $this->generateVehicleTable($licence);

        $view = $this->getViewModel(array('table' => $vehicleTable));
        $view->setTemplate('self-serve/layout/table');

        return $this->renderLayoutWithSubSections($view, 'vehicle');
    }

    /**
     * Method to add vehicle
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToVehicles();
        }

        $form = $this->generateForm(
            'vehicle', 'processGoodsVehicleForm'
        );

        $form->get('data')->setLabel('Add vehicle');

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/layout/form');

        return $this->renderLayoutWithSubSections($view, 'vehicle');
    }

    /**
     * Method to edit a vehicle.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $vehicleId = $this->params()->fromRoute('vehicleId');

        $data = array(
            'id' => $vehicleId,
        );

        //get operating centre enetity based on applicationId and operatingCentreId
        $result = $this->makeRestCall('Vehicle', 'GET', $data);

        if (empty($result)) {
            return $this->notFoundAction();
        }

        //hydrate data
        $data = array(
            'data' => array(
                'id' => $result['id'],
                'version' => $result['version'],
                'vrm' => $result['vrm'],
                'plated_weight' => $result['platedWeight'],
                'body_type' => $result['bodyType']
            )
        );

        // generate form with data
        $form = $this->generateFormWithData(
            'vehicle', 'processGoodsVehicleForm', $data
        );

        $form->get('data')->setLabel('Edit vehicle');
        $form->get('form-actions')->remove('addAnother');

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/layout/form');

        return $this->renderLayoutWithSubSections($view, 'vehicle');
    }

    /**
     * Performs delete action
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        die();
        $vehicleId = $this->params()->fromRoute('vehicleId');
        $licence = $this->getLicenceEntity();

        //delete conditions
        $cond = array(
            'vehicle' => $vehicleId,
            'licence' => $licence['id'],
        );
        $licenceVehicle = $this->makeRestCall('LicenceVehicle', 'GET', $cond);

        if ($licenceVehicle['Count'] == 0) {
            return $this->notFoundAction();
        }
        $licenceVehicle = $licenceVehicle['Results'][0];
        $this->makeRestCall('LicenceVehicle', 'DELETE', ['id' => $licenceVehicle['id']]);

        $this->makeRestCall('Vehicle', 'DELETE', ['id' => $vehicleId]);

        return $this->redirectToVehicles();
    }

    /**
     * Redirect to vehicles sections
     *
     * @return objecy
     */
    private function redirectToVehicles()
    {
        $applicationId = $this->getApplicationId();

        return $this->redirect()->toRoute('selfserve/vehicle-safety/vehicle', array('applicationId' => $applicationId));
    }

    /**
     * Process goods vehicle form
     *
     * @param array $validData
     * @param \Zend\Form\Form $form
     * @return \Zend\Form
     */
    public function processGoodsVehicleForm($validData, \Zend\Form\Form $form)
    {
        $data = $validData['data'];
        $saveResult = $this->persistVehicle($data);

        if ($saveResult) {
            $this->determineRedirect();
        }

        return $form;
    }

    /**
     * Method to examine the submit_ button that has been pressed and redirect
     * to the correct route.
     */
    private function determineRedirect()
    {
        $applicationId = $this->getApplicationId();

        if ($this->isButtonPressed('addAnother')) {
            $this->redirect()->toRoute(
                'selfserve/vehicle-safety/vehicle',
                array(
                    'applicationId' => $applicationId,
                    'action' => 'add'
                )
            );
        } else {
            return $this->redirectToVehicles();
        }
    }

    /**
     * Method to persist the vehicle and licence vehicle entity data
     *
     * @param array $validData
     * @throws \RuntimeException
     */
    private function persistVehicle($validData)
    {
        if (isset($validData['id']) && is_numeric($validData['id'])) {
            $this->updateVehicle($validData);
        } else {
            $this->createVehicle($validData);
        }

        return true;
    }

    /**
     * Method to create the vehicle Entity onlt
     *
     * @param array $validData ['id' => ?]
     * @return array containing id
     */
    private function createVehicle($validData)
    {
        $vehicleData = $this->mapVehicleData($validData);
        $vehicle = $this->makeRestCall('Vehicle', 'POST', $vehicleData);

        $licence = $this->getLicenceEntity();

        $this->createLicenceVehicle($licence, $vehicle);

        return $vehicle;
    }

    /**
     * Method to update a vehicle Entity
     *
     * @param array $validData ['id' => ?]
     * @return array containing id
     */
    private function updateVehicle($validData)
    {
        $vehicleData = $this->mapVehicleData($validData);
        $vehicle = $this->makeRestCall('Vehicle', 'PUT', $vehicleData);

        return $vehicle;
    }

    /**
     * Method to map form data to vehicle data
     *
     * @param array $validData form data
     * @return array entity data
     */
    private function mapVehicleData($validData)
    {
        $vehicleData = array(
            'id' => $validData['id'],
            'version' => $validData['version'],
            'vrm' => $validData['vrm'],
            'platedWeight' => (int) $validData['plated_weight'],
            'bodyType' => 'vhl_body_type.flat', //$validData['body_type'], //NOT PART OF THE STORY (2057)
            'isTipper' => 0,
            'isRefrigerated' => 0,
            'isArticulated' => 0,
            'certificateNumber' => '',
            'viAction' => ''
        );
        return $vehicleData;
    }

    /**
     * Method to map form data to licencevehicle data
     *
     * @param array $validData form data
     * @return array entity data
     */
    private function mapLicenceVehicleData($licence, $vehicle)
    {
        $licenceVehicleData = array(
            'licence' => $licence['id'],
            'dateApplicationReceived' => date('Y-m-d H:i:s'),
            'vehicle' => $vehicle['id'],
            'version' => 1
        );
        return $licenceVehicleData;
    }

    /**
     * Method to create the licence vehicle in the database
     *
     * @param array $licence licence data
     * @param array $vehicle vehicle data
     */
    private function createLicenceVehicle($licence, $vehicle)
    {
        $licenceVehicleData = $this->mapLicenceVehicleData($licence, $vehicle);
        $this->makeRestCall('LicenceVehicle', 'POST', $licenceVehicleData);
    }

    /**
     * Method to return the vehicle table for a licence.
     *
     * @param array $licence
     * @return string HTML table
     */
    public function generateVehicleTable($licence)
    {
        $results = $this->makeRestCall('LicenceVehicle', 'GET', array('licence' => $licence['id']));

        $settings = array(
            'sort' => 'field',
            'order' => 'ASC',
            'limit' => 10,
            'page' => 1,
            'url' => $this->getPluginManager()->get('url')
        );

        $table = $this->getServiceLocator()->get('Table')->buildTable('vehicle', $results, $settings);
        return $table;
    }
}
