<?php

/**
 * OperatingCentre Controller
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Finance;

use Zend\View\Model\ViewModel;

/**
 * OperatingCentre Controller
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentreController extends AbstractFinanceController
{
    /**
     * Index action
     *
     * @return object
     */
    public function indexAction()
    {
        $action = $this->checkForCrudAction();

        if ($action !== false) {
            return $action;
        }

        $applicationId = $this->params()->fromRoute('applicationId');

        $results = $this->makeRestCall('ApplicationOperatingCentre', 'GET', array('applicationId' => $applicationId));

        $table = $this->getOperatingCentreTable($results);

        $bundle = array(
            'properties' => array(
                'version',
                'tot_auth_vehicles',
                'tot_auth_trailers'
            )
        );

        $data = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);

        if (empty($data)) {
            return $this->notFoundAction();
        }

        $data['data'] = $data;

        $data['data']['noOfOperatingCentres'] = count($results);
        $data['data']['minVehicleAuth'] = 0;
        $data['data']['maxVehicleAuth'] = 0;
        $data['data']['minTrailerAuth'] = 0;
        $data['data']['maxTrailerAuth'] = 0;

        foreach ($results as $row) {

            $data['data']['minVehicleAuth'] = max(array($data['data']['minVehicleAuth'], $row['no_of_vehicles_required']));
            $data['data']['minTrailerAuth'] = max(array($data['data']['minTrailerAuth'], $row['no_of_trailers_required']));
            $data['data']['maxVehicleAuth'] += (int)$row['no_of_vehicles_required'];
            $data['data']['maxTrailerAuth'] += (int)$row['no_of_trailers_required'];
        }

        $form = $this->generateFormWithData('operating-centre-authorisation', 'processAuthorisation', $data);

        $view = new ViewModel(array('operatingCentres' => $table, 'form' => $form));

        $view->setTemplate('self-serve/finance/operating-centre/index');

        return $this->renderLayout($view, 'operatingCentre');
    }

    /**
     * Process persisting of Authorisation
     *
     * @param array $data
     */
    public function processAuthorisation($data)
    {
        print '<pre>';
        print_r($data);
        print '</pre>';
        exit;
    }

    /**
     * Get the operating centre table
     *
     * @param array $results
     * @return object
     */
    private function getOperatingCentreTable($results)
    {
        $settings = array(
            'sort' => 'address',
            'order' => 'ASC',
            'limit' => 10,
            'page' => 1,
            'url' => $this->getPluginManager()->get('url')
        );

        return $this->getServiceLocator()->get('Table')->buildTable('operatingcentre', $results, $settings);
    }

    /**
     * Action that is responsible for adding operating centre
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $form = $this->generateForm(
            'operating-centre', 'processAddForm'
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/finance/operating-centre/add');
        return $this->renderLayout($view, 'operatingCentre');
    }

    /**
     * Action that is responsible for editing operating centre
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $operatingCentreId = $this->params()->fromRoute('operatingCentreId');
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $operatingCentreId,
            'application' => $applicationId,
        );

        //get operating centre enetity based on applicationId and operatingCentreId
        $result = $this->makeRestCall('ApplicationOperatingCentre', 'GET', $data);
        if (empty($result)) {
            return $this->notFoundAction();
        }

        //hydrate data
        $data = array(
            'version' => $result['version'],
            'authorised-vehicles' => array(
                'no-of-vehicles' => $result['numberOfVehicles'],
                'no-of-trailers' => $result['numberOfTrailers'],
                'parking-spaces-confirmation' => $result['sufficientParking'],
                'permission-confirmation' => $result['permission'],
            ),
        );

        //generate form with data
        $form = $this->generateFormWithData(
            'operating-centre', 'processEditForm', $data
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/finance/operating-centre/edit');
        return $this->renderLayout($view, 'operatingCentre');
    }

    /**
     * Persist data to database. After that, redirect to Operating centres page
     *
     * @param array $validData
     * @return void
     */
    public function processAddForm($validData)
    {
        $data = array(
            'version' => 1,
        );
        $data = array_merge($this->mapData($validData), $data);

        //persiste to database by calling rest api
        $result = $this->makeRestCall('ApplicationOperatingCentre', 'POST', $data);
        if (isset($result['id'])) {
            $this->redirect()->toRoute('selfserve/finance', array(), true);
        }
    }

    /**
     * Persist data to database. After that, redirect to Operating centres page
     *
     * @param array $validData
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function processEditForm($validData)
    {
        $operatingCentreId = $this->params()->fromRoute('operatingCentreId');
        $data = array(
            'id' => $operatingCentreId,
            'version' => $validData['version'],
        );
        $data = array_merge($this->mapData($validData), $data);

        //persiste to database by calling rest api
        $result = $this->makeRestCall('ApplicationOperatingCentre', 'PUT', $data);
        return $this->redirect()->toRoute('selfserve/finance', array(), true);
    }

    /**
     * Map common data
     * @param array $validData
     * @return array
     */
    private function mapData($validData)
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        return array(
            'numberOfVehicles' => $validData['authorised-vehicles']['no-of-vehicles'],
            'numberOfTrailers' => $validData['authorised-vehicles']['no-of-trailers'],
            'sufficientParking' => $validData['authorised-vehicles']['parking-spaces-confirmation'],
            'permission' => $validData['authorised-vehicles']['permission-confirmation'],
            'application' => $applicationId,
        );
    }

    public function completeAction()
    {

    }
}
