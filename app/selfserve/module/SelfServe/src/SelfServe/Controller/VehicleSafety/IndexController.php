<?php

/**
 * Vehicles & Safety  Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\VehicleSafety;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

/**
 * Vehicles & Safety  Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class IndexController extends FormJourneyActionController
{

    /**
     * Construct the Vehicles Safety Controller class
     * Sets the current section only.
     */
    public function __construct()
    {
        $this->setCurrentSection('vehicle-safety');
    }

    /**
     * Generates the next step form depending on which step the user is on.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $licence = $this->getLicenceEntity();
        $vehicleTable = $this->generateVehicleTable($licence);

        $action = $this->getRequest()->getPost('action');

        // process any submit button pressed
        if (isset($action)) {

            switch ($action) {
                case 'Add':
                    $this->redirectToVehicleAction($action);
                    break;
                case 'Edit':
                    // todo validation
                    $this->redirectToVehicleAction($action);
                    break;
                case 'Delete':
                    // todo validation
                    $this->redirectToVehicleAction($action);
                    break;
            }
        }

        // collect completion status
        $completionStatus = $this->makeRestCall('ApplicationCompletion', 'GET', array('application_id' => $applicationId));

        // render the view
        $view = new ViewModel(['vehicleTable' => $vehicleTable,
                                'completionStatus' => (($completionStatus['Count']>0)?$completionStatus['Results'][0]:Array()),
                                'applicationId' => $applicationId]);
        $view->setTemplate('self-serve/vehicle-safety/index');
        return $view;
    }

    /**
     * Method to redirect user depending on action
     *
     * @param string $action
     */
    private function redirectToVehicleAction($action)
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $vehicleId = $this->getRequest()->getPost('id');

        return $this->redirect()->toRoute(
            'selfserve/vehicle-safety/vehicle-action/vehicle-' . strtolower($action),
            array(
                'action' => $action,
                'vehicleId' => $vehicleId,
                'applicationId' => $applicationId
            )
        );
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

    /**
     * End of the journey redirect to the next step TBC
     */
    public function completeAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        // persist data if possible
        $this->redirect()->toRoute(
            'selfserve/transport',
            array(
                'applicationId' => $applicationId,
                'step' => 'index'
            )
        );
    }
}
