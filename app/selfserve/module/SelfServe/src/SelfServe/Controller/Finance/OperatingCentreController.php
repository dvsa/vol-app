<?php

/**
 * OperatingCentre Controller
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Finance;

/**
 * OperatingCentre Controller
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentreController extends AbstractFinanceController
{

    private $isPsv = null;

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

        $results = $this->getOperatingCentresForApplication($applicationId);
        $flatResults = $this->convertOperatingCentresDataToFlatFormat($results);

        $table = $this->getOperatingCentreTable($flatResults, $applicationId);

        $data = $this->formatDataForForm($data, $applicationId, $flatResults);

        $form = $this->generateFormWithData(
            $this->processConfigName('operating-centre-authorisation', $applicationId),
            'processAuthorisation',
            $data,
            true
        );

        $form->get('form-actions')->get('home')->setValue($this->getUrlFromRoute(
            'selfserve/business-type',
            ['applicationId' => $applicationId, 'step' => 'details']
        ));

        $view = $this->getViewModel(
            array(
                'operatingCentres' => $table,
                'form' => $form,
                'isPsv' => $this->isPsvLicence($applicationId)
            )
        );

        $view->setTemplate('self-serve/finance/operating-centre/index');

        return $this->renderLayoutWithSubSections($view, 'operatingCentre');
    }

    /**
     * Action that is responsible for adding operating centre
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        if ($this->isButtonPressed('cancel')) {

            return $this->backToOperatingCentre();
        }

        $applicationId = $this->params()->fromRoute('applicationId');

        $form = $this->generateForm(
            $this->processConfigName('operating-centre', $applicationId),
            'processAddForm'
        );

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/finance/operating-centre/add');

        return $this->renderLayoutWithSubSections($view, 'operatingCentre');
    }

    /**
     * Action that is responsible for editing operating centre
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        if ($this->isButtonPressed('cancel')) {

            return $this->backToOperatingCentre();
        }

        $operatingCentreId = $this->params()->fromRoute('id');
        $applicationId = $this->params()->fromRoute('applicationId');

        //get operating centre entity based on applicationId and operatingCentreId
        $result = $this->makeRestCall('ApplicationOperatingCentre', 'GET', array('id' => $operatingCentreId));

        if (empty($result)) {
            return $this->notFoundAction();
        }
        $resultsOperatingCentre = $this->getOperatingCentresForApplication($applicationId);
        if (empty($resultsOperatingCentre) || !count($resultsOperatingCentre['Results'])) {
            return $this->notFoundAction();
        }
        $resultsOperatingCentre = current($resultsOperatingCentre['Results']);

        $data = array(
            'version' => $result['version'],
            'authorised-vehicles' => array(
                'no-of-vehicles' => $result['numberOfVehicles'],
                'no-of-trailers' => $result['numberOfTrailers'],
                'parking-spaces-confirmation' => $result['sufficientParking'],
                'permission-confirmation' => $result['permission']
            ),
            'address' => array(
                'id' => $resultsOperatingCentre['operatingCentre']['address']['id'],
                'version' => $resultsOperatingCentre['operatingCentre']['address']['version'],
                'addressLine1' => $resultsOperatingCentre['operatingCentre']['address']['addressLine1'],
                'addressLine2' => $resultsOperatingCentre['operatingCentre']['address']['addressLine2'],
                'addressLine3' => $resultsOperatingCentre['operatingCentre']['address']['addressLine3'],
                'addressLine4' => $resultsOperatingCentre['operatingCentre']['address']['addressLine4'],
                'postcode' => $resultsOperatingCentre['operatingCentre']['address']['postcode'],
                'county' => $resultsOperatingCentre['operatingCentre']['address']['county'],
                'city' => $resultsOperatingCentre['operatingCentre']['address']['city'],
                'country' => 'country.' . $resultsOperatingCentre['operatingCentre']['address']['country']
            )
        );

        $form = $this->generateFormWithData(
            $this->processConfigName(
                'operating-centre',
                $applicationId
            ),
            'processEditForm',
            $data,
            true
        );

        $form->get('form-actions')->remove('addAnother');

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/finance/operating-centre/edit');

        return $this->renderLayoutWithSubSections($view, 'operatingCentre');
    }

    /**
     * Delete an operating centre
     */
    public function deleteAction()
    {
        $appOperatingCentreId = $this->params()->fromRoute('id');

        $data = array('id' => $appOperatingCentreId);

        $bundle = array('properties' => array('id'));

        $result = $this->makeRestCall('ApplicationOperatingCentre', 'GET', $data, $bundle);

        if (empty($result)) {
            return $this->notFoundAction();
        }

        $this->makeRestCall('ApplicationOperatingCentre', 'DELETE', array('id' => $result['id']));

        return $this->backToOperatingCentre();
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
                                'id',
                                'version',
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
        
        return $data;
    }

    /**
     * Converts operating centres details to flat format
     *
     * @param array $data
     * @return array
     */
    public function convertOperatingCentresDataToFlatFormat($data)
    {
        $newData = array();

        foreach ($data['Results'] as $row) {

            $newRow = $row;

            if (isset($row['operatingCentre']['address'])) {

                unset($row['operatingCentre']['address']['id']);
                unset($row['operatingCentre']['address']['version']);

                $newRow = array_merge($newRow, $row['operatingCentre']['address']);
            }

            unset($newRow['operatingCentre']);

            $newData[] = $newRow;
        }

        return $newData;
    }

    /**
     * Get the operating centre table
     *
     * @param array $results
     * @return object
     */
    private function getOperatingCentreTable($results, $applicationId)
    {
        $settings = array(
            'sort' => 'address',
            'order' => 'ASC',
            'limit' => 10,
            'page' => 1,
            'url' => $this->getPluginManager()->get('url')
        );

        return $this->getServiceLocator()->get('Table')->buildTable(
            $this->processConfigName('operatingcentre', $applicationId),
            $results,
            $settings
        );
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

        return $this->redirect()->toRoute(
            'selfserve/finance/financial_evidence',
            array('applicationId' => $data['id'])
        );
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
     * Persist data to database. After that, redirect to Operating centres page
     *
     * @param array $validData
     *
     * @return void
     */
    public function processAddForm($validData)
    {
        $data = array(
            'version' => 1,
            'adPlaced' => 1
        );

        $data = array_merge($this->mapData($validData), $data);

        // first of all create the basic operating centre; this doesn't
        // store much data except version and address...
        $result = $this->makeRestCall('OperatingCentre', 'POST', $data);

        // ... and then create the application OC entity which persists the
        // rest of the data
        $data['operatingCentre'] = $result['id'];
        $result = $this->makeRestCall('ApplicationOperatingCentre', 'POST', $data);

        if (isset($result['id'])) {
            if ($this->isButtonPressed('addAnother')) {

                return $this->redirectToRoute(null, array(), array(), true);
            }

            return $this->backToOperatingCentre();
        }
    }

    /**
     * Persist data to database. After that, redirect to Operating centres page
     *
     * @param array $validData
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function processEditForm($validData)
    {
        $operatingCentreId = $this->params()->fromRoute('id');

        $data = array(
            'id' => $operatingCentreId,
            'version' => $validData['version'],
        );

        $validData['address']['country'] = str_replace('country.', '', $validData['address']['country']);
        //saving address
        $this->makeRestCall('Address', 'PUT', $validData['address']);

        $data = array_merge($this->mapData($validData), $data);

        $this->makeRestCall('ApplicationOperatingCentre', 'PUT', $data);

        return $this->backToOperatingCentre();
    }

    /**
     * Map common data
     * @param array $validData
     *
     * @return array
     */
    private function mapData($validData)
    {
        $validData = $this->processAddressData($validData);

        $applicationId = $this->params()->fromRoute('applicationId');
        $data = array(
            'numberOfVehicles' => $validData['authorised-vehicles']['no-of-vehicles'],
            'sufficientParking' => $validData['authorised-vehicles']['parking-spaces-confirmation'],
            'permission' => $validData['authorised-vehicles']['permission-confirmation'],
            'adPlaced' => true,
            'application' => $applicationId,
            'addresses' => $validData['addresses'],
        );

        //licence type condition
        if (isset($validData['authorised-vehicles']['no-of-trailers'])) {
            $data = array_merge(
                $data,
                array(
                    'numberOfTrailers' => $validData['authorised-vehicles']['no-of-trailers']
                )
            );
        }

        return $data;
    }

    /**
     * Check if licence type is psv
     *
     * @param int $applicationId
     * @return boolean
     */
    private function isPsvLicence($applicationId)
    {
        if (is_null($this->isPsv)) {
            $licence = $this->getLicenceEntity($applicationId);
            $this->isPsv = $licence['goodsOrPsv'] == 'psv';
        }
        return $this->isPsv;
    }

    /**
     * Adds -psv suffix if the licence type is psv
     *
     * @param $applicationId
     * @param $name
     * @return string
     */
    private function processConfigName($name, $applicationId)
    {
        if ($this->isPsvLicence($applicationId)) {
            $name .= '-psv';
        }
        return $name;
    }

    /**
     * Redirect to operating centre
     */
    public function backToOperatingCentre()
    {
        $applicationId = $this->getApplicationId();

        return $this->redirect()->toRoute(
            'selfserve/finance/operating_centre',
            array('applicationId' => $applicationId)
        );
    }
}
