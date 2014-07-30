<?php

/**
 * Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TaxiPhv;

/**
 * Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceController extends TaxiPhvController
{
    /**
     * Holds the sub action service
     *
     * @var string
     */
    protected $actionService = 'PrivateHireLicence';

    /**
     * Holds the table data
     *
     * @var array
     */
    protected $tableData;

    /**
     * Form tables
     *
     * @var array
     */
    protected $formTables = array(
        'table' => 'application_taxi-phv_licence-form'
    );

    /**
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        '_addresses' => array(
            'address'
        ),
        'main' => array(
            'children' => array(
                'privateHireLicence' => array(
                    'mapFrom' => array(
                        'data'
                    )
                ),
                'contactDetails' => array(
                    'mapFrom' => array(
                        'contactDetails'
                    ),
                    'children' => array(
                        'addresses' => array(
                            'mapFrom' => array(
                                'addresses'
                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * Holds the actionDataBundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'privateHireLicenceNumber',
        ),
        'children' => array(
            'contactDetails' => array(
                'properties' => array(
                    'id',
                    'version',
                    'description'
                ),
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

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Add licence
     */
    public function addAction()
    {
        return $this->renderSection();
    }

    /**
     * Edit licence
     */
    public function editAction()
    {
        return $this->renderSection();
    }

    /**
     * Delete licence
     *
     * @return Response
     */
    public function deleteAction()
    {
        return $this->delete();
    }

    /**
     * Get table data
     *
     * @return array
     */
    protected function getFormTableData()
    {
        if (is_null($this->tableData)) {

            $licence = $this->getLicenceData();

            $data = $this->makeRestCall(
                'PrivateHireLicence',
                'GET',
                array('licence' => $licence['id']),
                $this->getActionDataBundle()
            );

            $newData = array();

            foreach ($data['Results'] as $row) {

                $newRow = array(
                    'id' => $row['id'],
                    'privateHireLicenceNumber' => $row['privateHireLicenceNumber'],
                    'councilName' => $row['contactDetails']['description']
                );

                unset($row['contactDetails']['address']['id']);
                unset($row['contactDetails']['address']['version']);

                $newData[] = array_merge($newRow, $row['contactDetails']['address']);
            }

            $this->tableData = $newData;
        }

        return $this->tableData;
    }

    /**
     * Process the action load data
     *
     * @param array $oldData
     */
    protected function processActionLoad($oldData)
    {
        $data['data'] = $oldData;

        if ($this->getActionName() != 'add') {

            $data['contactDetails'] = $oldData['contactDetails'];
            $data['address'] = $oldData['contactDetails']['address'];
            $data['address']['country'] = 'country.' . $data['address']['country'];
        }

        $licenceData = $this->getLicenceData();

        $data['data']['licence'] = $licenceData['id'];

        return $data;
    }

    /**
     * Save the licence
     *
     * @param array $data
     * @param string $service
     * @return null|Response
     */
    protected function actionSave($data, $service = null)
    {
        $data['contactDetails']['contactDetailsType'] = 'Council';

        $results = parent::actionSave($data['contactDetails'], 'ContactDetails');

        if (!empty($data['contactDetails']['id'])) {
            $contactDetailsId = $data['contactDetails']['id'];
        } elseif (isset($results['id'])) {
            $contactDetailsId = $results['id'];
        } else {
            /**
             * @todo Handle failure to save contactDetails. For now we just throw an exception until the story has been
             * complete which encompassess feeding back errors to the user
             */
            throw new \Exception('Unable to save contact details');
        }

        $data['privateHireLicence']['contactDetails'] = $contactDetailsId;

        parent::actionSave($data['privateHireLicence'], $service);
    }

    /**
     * Overrides the abstract save method which normally tries to automatically save the application, we don't need
     * to save anything so we just return
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        return;
    }
}
