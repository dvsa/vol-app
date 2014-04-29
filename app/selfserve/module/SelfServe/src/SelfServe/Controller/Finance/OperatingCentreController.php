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

        $results = $this->getOperatingCentresForApplication($applicationId);

        $table = $this->getOperatingCentreTable($results);

        $bundle = array(
            'properties' => array(
                'version',
                'totAuthVehicles',
                'totAuthTrailers'
            )
        );

        $data = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);

        if (empty($data)) {
            return $this->notFoundAction();
        }

        $data = $this->formatDataForForm($data, $applicationId, $results);

        $form = $this->generateFormWithData('operating-centre-authorisation', 'processAuthorisation', $data);

        $view = new ViewModel(array('operatingCentres' => $table, 'form' => $form));

        $view->setTemplate('self-serve/finance/operating-centre/index');

        return $this->renderLayout($view, 'operatingCentre');
    }

    /**
     * Get operating centres for application
     *
     * @param int $applicationId
     * @return array
     */
    private function getOperatingCentresForApplication($applicationId)
    {
        $bundle = array(
            'properties' => array(
                'id',
                'numberOfTrailers',
                'numberOfVehicles',
                'permission',
                'adPlaced'
            ),
            'children' => array(
                'operatingCentre' => array(
                    'properties' => array('id'),
                    'children' => array(
                        'address' => array(
                            'properties' => array(
                                'addressLine1',
                                'addressLine2',
                                'addressLine3',
                                'addressLine4',
                                'postcode',
                                'county',
                                'city',
                                'country'
                            )
                        )
                    )
                )
            )
        );

        $data = $this->makeRestCall(
            'ApplicationOperatingCentre',
            'GET',
            array('application' => $applicationId),
            $bundle
        );

        $newData = array();

        print '<pre>';
        print_r($data);
        print '</pre>';
        exit;

        foreach ($data as $row) {

            $newRow = $row;

            if (isset($row['operatingCentre']['address'])) {

                $newRow = array_merge($newRow, $row['operatingCentre']['address']);
            }

            unset($newRow['operatingCentre']);

            $newData[] = $newRow;
        }

        return $newData;
    }

    /**
     * Process persisting of Authorisation
     *
     * @param array $data
     */
    public function processAuthorisation($data)
    {
        $data = $this->formatDataFromForm($data);

        $this->makeRestCall('Application', 'PUT', $data);

        return $this->redirect()->toRoute('selfserve/finance/financial_evidence', array('applicationId' => $data['id']));
    }

    /**
     * Format the data from the form
     *
     * @param array $data
     * @return array
     */
    private function formatDataFromForm($data)
    {
        $data = $data['data'];

        unset($data['noOfOperatingCentres']);
        unset($data['minVehicleAuth']);
        unset($data['maxVehicleAuth']);
        unset($data['minTrailerAuth']);
        unset($data['maxTrailerAuth']);

        return $data;
    }

    /**
     * Format the data for the form
     *
     * @param array $data
     * @param int $applicationId
     * @return array
     */
    private function formatDataForForm($data, $applicationId, $results)
    {
        $data['data'] = $data;
        $data['data']['id'] = $applicationId;

        // These fields are used for validation
        $data['data']['noOfOperatingCentres'] = count($results);
        $data['data']['minVehicleAuth'] = 0;
        $data['data']['maxVehicleAuth'] = 0;
        $data['data']['minTrailerAuth'] = 0;
        $data['data']['maxTrailerAuth'] = 0;

        foreach ($results as $row) {

            $data['data']['minVehicleAuth'] = max(
                array($data['data']['minVehicleAuth'], $row['numberOfVehicles'])
            );
            $data['data']['minTrailerAuth'] = max(
                array($data['data']['minTrailerAuth'], $row['numberOfTrailers'])
            );
            $data['data']['maxVehicleAuth'] += (int) $row['numberOfVehicles'];
            $data['data']['maxTrailerAuth'] += (int) $row['numberOfTrailers'];
        }

        return $data;
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

        $operatingCentreId = $this->params()->fromRoute('id');

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
                'permission-confirmation' => $result['permission']
            )
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
     * Delete an operating centre
     */
    public function deleteAction()
    {
        $operatingCentreId = $this->params()->fromRoute('id');
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'id' => $operatingCentreId,
            'application' => $applicationId,
        );

        $result = $this->makeRestCall('ApplicationOperatingCentre', 'GET', $data);

        if (empty($result)) {

            return $this->notFoundAction();
        }

        $this->makeRestCall('OperatingCentre', 'DELETE', array('id' => $operatingCentreId));

        return $this->redirect()->toRoute(
            'selfserve/finance/operating_centre',
            array('applicationId' => $applicationId)
        );
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
        $operatingCentreId = $this->params()->fromRoute('id');

        $data = array(
            'id' => $operatingCentreId,
            'version' => $validData['version'],
        );
        $data = array_merge($this->mapData($validData), $data);

        //persist to database by calling rest api
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
