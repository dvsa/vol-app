<?php

/**
 * Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Application\VehicleSafety;

/**
 * Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends VehicleSafetyController
{

    /**
     * Cache the data
     *
     * @var array
     */
    private $data = array();

    /**
     * Form tables
     *
     * @var array
     */
    protected $formTables = array(
        'table' => 'safety-inspection-providers'
    );

    /**
     * Data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
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
                                    'addressLine4',
                                    'town',
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

    /**
     * Holds the action service
     *
     * @var string
     */
    protected $actionService = 'Workshop';

    /**
     * Holds the action data bundle
     *
     * @var array
     */
    protected $actionDataBundle = array(
        'properties' => array(
            'id',
            'version',
            'isExternal'
        ),
        'children' => array(
            'contactDetails' => array(
                'properties' => array(
                    'id',
                    'version',
                    'fao'
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
                            'town',
                            'country',
                            'postcode'
                        )
                    )
                )
            )
        )
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
                'workshop' => array(
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

    protected $dataMap = null;

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
     * Delete sub action
     *
     * @return Response
     */
    public function deleteAction()
    {
        return $this->delete();
    }

    /**
     * Action save
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $data['contactDetails']['contactType'] = 'ct_work';
        $saved = parent::actionSave($data['contactDetails'], 'ContactDetails');

        if ($this->getActionName() == 'add') {

            if (!isset($saved['id'])) {
                throw new \Exception('Unable to save contact details');
            }

            $data['workshop']['contactDetails'] = $saved['id'];
        }

        parent::actionSave($data['workshop'], 'Workshop');
    }

    /**
     * Process action load data
     *
     * @param array $data
     * @return array
     */
    protected function processActionLoad($data)
    {
        $data = parent::processActionLoad($data);

        if ($this->getActionName() != 'add') {
            $data['data'] = array(
                'id' => $data['id'],
                'version' => $data['version'],
                'isExternal' => $data['isExternal']
            );

            $data['address'] = $data['contactDetails']['address'];
            $data['address']['country'] = 'country.' . $data['address']['country'];

            unset($data['id']);
            unset($data['version']);
            unset($data['isExternal']);
            unset($data['contactDetails']['address']);
        }

        $data['data']['application'] = $this->getIdentifier();

        return $data;
    }

    /**
     * Get the form table data
     *
     * @param int $id
     * @param string $name
     */
    protected function getFormTableData($id, $name)
    {
        unset($name);

        $this->load($id);

        $data = $this->data['workshops'];

        $tableData = array();

        foreach ($data as $workshop) {

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
     * Remove the trailer fields for PSV
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        if ($this->isPsv()) {
            $form->get('licence')->remove('safetyInsTrailers');

            $label = $form->get('licence')->get('safetyInsVaries')->getLabel();
            $form->get('licence')->get('safetyInsVaries')->setLabel($label . '.psv');

            $table = $form->get('table')->get('table')->getTable();

            $emptyMessage = $table->getVariable('empty_message');
            $table->setVariable('empty_message', $emptyMessage . '-psv');

            $form->get('table')->get('table')->setTable($table);
        }

        return $form;
    }

    /**
     * Load the data for the form
     *
     * @param arary $data
     * @return array
     */
    protected function processLoad($data)
    {
        $data['application'] = array(
            'id' => $data['id'],
            'version' => $data['version'],
            'safetyConfirmation' => $data['safetyConfirmation']
        );

        unset($data['id']);
        unset($data['version']);
        unset($data['safetyConfirmation']);

        $data['licence']['safetyInsVehicles'] = 'inspection_interval_vehicle.' . $data['licence']['safetyInsVehicles'];

        $data['licence']['tachographIns'] = 'tachograph_analyser.' . $data['licence']['tachographIns'];

        $data['licence']['safetyInsTrailers'] = 'inspection_interval_trailer.' . $data['licence']['safetyInsTrailers'];

        return $data;
    }

    /**
     * Save the form data
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
        $data['licence']['safetyInsVehicles'] = str_replace(
            'inspection_interval_vehicle.', '', $data['licence']['safetyInsVehicles']
        );

        $data['licence']['tachographIns'] = str_replace(
            'tachograph_analyser.', '', $data['licence']['tachographIns']
        );

        if (isset($data['licence']['safetyInsTrailers'])) {
            $data['licence']['safetyInsTrailers'] = str_replace(
                'inspection_interval_trailer.', '', $data['licence']['safetyInsTrailers']
            );
        }

        parent::save($data['licence'], 'Licence');

        parent::save($data['application'], 'Application');
    }

    /**
     * Cache the loaded data
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        if (empty($this->data)) {
            $data = parent::load($id);

            $this->data = $data;
        }

        return $this->data;
    }
}
