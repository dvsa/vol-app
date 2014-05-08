<?php

/**
 * SafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\VehicleSafety;

use Zend\View\Model\ViewModel;

/**
 * SafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends AbstractVehicleSafetyController
{
    /**
     * Safety form
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        $bundle = array(
            'properties' => array(
                'version',
                'safetyConfirmation'
            ),
            'children' => array(
                'licence' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'safetyInsVehicles',
                        'safetyInsTrailers',
                        'safetyInsVaries',
                        'tachographIns',
                        'tachographInsName'
                    )
                ),
                'workshops' => array(
                    'properties' => array(
                        'id',
                        'isExternal'
                    ),
                    'children' => array(
                        'contactDetails' => array(
                            'properties' => array(
                                'fao'
                            ),
                            'children' => array(
                                'address' => array(
                                    'properties' => array(
                                        'addressLine1',
                                        'addressLine2',
                                        'addressLine3',
                                        'city',
                                        'country',
                                        'postcode'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        $data = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);

        if (empty($data)) {
            return $this->notFoundAction();
        }

        $tableData = $this->formatTableData($data);

        $form = $this->generateTableFormWithData(
            'vehicle-safety',
            array(
                'success' => 'processVehicleSafetySuccess',
                'crud_action' => 'processVehicleSafetyCrudAction'
            ),
            $this->formatDataForForm($data, $applicationId),
            array(
                'table' => array(
                    'config' => 'safety-inspection-providers',
                    'data' => $tableData
                )
            ),
            true
        );

        $form->get('form-actions')->get('home')->setValue($this->getUrlFromRoute('selfserve/dashboard-home'));

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/forms/generic');

        return $this->renderLayoutWithSubSections($view, 'safety');
    }

    /**
     * Format data for table
     *
     * @param array $data
     */
    private function formatTableData($data)
    {
        $tableData = array();

        foreach ($data['workshops'] as $workshop) {

            $row = $workshop;

            if (isset($row['contactDetails'])) {

                $row = array_merge($row, $row['contactDetails']);
                unset($row['contactDetails']);
            }

            if (isset($row['address'])) {

                $row = array_merge($row, $row['address']);
                unset($row['address']);
            }

            $tableData[] = $row;
        }

        return $tableData;
    }

    /**
     * Format data for the form
     *
     * @param array $data
     * @param int $applicationId
     * @return array
     */
    protected function formatDataForForm($data, $applicationId)
    {
        $newData = array(
            'licence' => array(
            ),
            'table' => array(
                'numberOfInspectionProviders' => count($data['workshops'])
            ),
            'application' => array(
                'id' => $applicationId,
                'version' => $data['version'],
                'safetyConfirmation' => isset($data['safetyConfirmation']) || $data['safetyConfirmation'] == 0
                    ? array()
                    : array('1')
            )
        );

        foreach ($data['licence'] as $key => $val) {
            $newData['licence']['licence.' . $key] = $val;
        }

        $newData['licence']['licence.safetyInsVehicles'] = 'inspection_interval_vehicle.'
            . $newData['licence']['licence.safetyInsVehicles'];
        $newData['licence']['licence.safetyInsTrailers'] = 'inspection_interval_trailer.'
            . $newData['licence']['licence.safetyInsTrailers'];
        $newData['licence']['licence.tachographIns'] = 'tachograph_analyser.'
            . $newData['licence']['licence.tachographIns'];

        return $newData;
    }

    /**
     * Add the redirect on success
     *
     * @param array $data
     * @return object
     */
    public function processVehicleSafetySuccess($data)
    {
        $applicationId = $this->getApplicationId();

        $this->persistVehicleSafetyData($data);

        return $this->redirectToRoute(
            'selfserve/previous-history',
            array('step' => 'finance', 'applicationId' => $applicationId)
        );
    }

    /**
     * Process the vehicle safety form
     *
     * @param array $data
     */
    public function processVehicleSafetyCrudAction($data)
    {
        $this->persistVehicleSafetyData($data);

        $data['table']['action'] = strtolower($data['table']['action']);

        if ($data['table']['action'] == 'add') {

            return $this->redirectToRoute(
                'selfserve/vehicle-safety/safety/workshop',
                array('action' => 'add', 'applicationId' => $data['application']['id'])
            );
        } else {

            if (!isset($data['table']['id']) || empty($data['table']['id'])) {

                return $this->crudActionMissingId();
            }

            return $this->redirectToRoute(
                'selfserve/vehicle-safety/safety/workshop',
                array(
                    'action' => $data['table']['action'],
                    'applicationId' => $data['application']['id'],
                    'id' => $data['table']['id']
                )
            );
        }
    }

    /**
     * Persist the vehicle safety data
     *
     * @param array $data
     */
    public function persistVehicleSafetyData($data)
    {
        $applicationId = $this->getApplicationId();

        $applicationData = $this->formatApplicationData($data, $applicationId);

        $licenceData = $this->formatLicenceData($data);

        $this->makeRestCall('Application', 'PUT', $applicationData);

        $this->makeRestCall('Licence', 'PUT', $licenceData);
    }

    /**
     * Format licence data for persisting
     *
     * @param array $data
     * @return array
     */
    private function formatLicenceData($data)
    {
        $licenceData = array();

        foreach ($data['licence'] as $key => $value) {

            if (strstr($key, 'licence.')) {

                $licenceData[str_replace('licence.', '', $key)] = $value;
            }
        }

        $licenceData['safetyInsVehicles'] = str_replace(
            'inspection_interval_vehicle.',
            '',
            $licenceData['safetyInsVehicles']
        );

        $licenceData['safetyInsTrailers'] = str_replace(
            'inspection_interval_trailer.',
            '',
            $licenceData['safetyInsTrailers']
        );

        $licenceData['tachographIns'] = str_replace('tachograph_analyser.', '', $licenceData['tachographIns']);

        return $licenceData;
    }

    /**
     * Format the application data for persisting
     *
     * @param array $data
     * @param int $applicationId
     * @return array
     */
    private function formatApplicationData($data, $applicationId)
    {
        return array(
            'id' => $applicationId,
            'version' => $data['application']['version'],
            'safetyConfirmation' => $data['application']['safetyConfirmation'][0]
        );
    }
}
