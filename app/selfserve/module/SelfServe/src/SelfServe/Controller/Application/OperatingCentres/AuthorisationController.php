<?php

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Application\OperatingCentres;

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthorisationController extends OperatingCentresController
{
    /**
     * Holds the table data
     *
     * @var array
     */
    private $tableData = null;

    /**
     * Holds the data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => array(
            'id',
            'version',
            'totAuthSmallVehicles',
            'totAuthMediumVehicles',
            'totAuthLargeVehicles',
            'totCommunityLicences',
            'totAuthVehicles',
            'totAuthTrailers'
        )
    );

    /**
     * Holds the sub action service
     *
     * @var string
     */
    protected $actionService = 'ApplicationOperatingCentre';

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
                'applicationOperatingCentre' => array(
                    'mapFrom' => array(
                        'data'
                    )
                ),
                'operatingCentre' => array(
                    'mapFrom' => array(
                        'operatingCentre'
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
            'numberOfTrailers',
            'numberOfVehicles',
            'sufficientParking',
            'permission',
            'adPlaced'
        ),
        'children' => array(
            'operatingCentre' => array(
                'properties' => array(
                    'id',
                    'version'
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
     * Remove trailer related columns for PSV
     *
     * @param object $table
     * @return object
     */
    protected function alterTable($table)
    {
        if ($this->isPsv()) {
            $cols = $table->getColumns();

            unset($cols['trailersCol']);

            $table->setColumns($cols);

            $footer = $table->getFooter();

            $footer['total']['content'] .= '-psv';

            unset($footer['trailersCol']);

            $table->setFooter($footer);
        }

        return $table;
    }

    /**
     * Remove trailer elements for PSV
     *
     * @param object $form
     * @return object
     */
    protected function alterForm($form)
    {
        if (!in_array($this->getLicenceType(), array('standard-national', 'standard-international'))) {
            $form->get('data')->remove('totAuthLargeVehicles');
        }

        if (!in_array($this->getLicenceType(), array('standard-international', 'restricted'))) {
            $form->get('data')->remove('totCommunityLicences');
        }

        if ($this->isPsv()) {

            //$form->get('data')->remove('totAuthTrailers');
            $form->get('data')->remove('minTrailerAuth');
            $form->get('data')->remove('maxTrailerAuth');
        }
        return $form;
    }

    /**
     * Remove trailers for PSV
     *
     * @param Form $form
     */
    protected function alterActionForm($form)
    {
        if ($this->isPsv()) {
            $form->get('data')->remove('numberOfTrailers');

            $label = $form->get('data')->getLabel();
            $form->get('data')->setLabel($label .= '-psv');

            $label = $form->get('data')->get('sufficientParking')->getLabel();
            $form->get('data')->get('sufficientParking')->setLabel($label .= '-psv');

            $label = $form->get('data')->get('permission')->getLabel();
            $form->get('data')->get('permission')->setLabel($label .= '-psv');
        }

        return $form;
    }

    /**
     * Get table data
     *
     * @param int $id
     * @return array
     */
    protected function getTableData($id)
    {
        if (is_null($this->tableData)) {
            $data = $this->makeRestCall(
                'ApplicationOperatingCentre',
                'GET',
                array('application' => $id),
                $this->getActionDataBundle()
            );

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

            $this->tableData = $newData;
        }

        return $this->tableData;
    }

    /**
     * Save the operating centre
     *
     * @param array $data
     * @param string $service
     * @return null|Response
     */
    protected function actionSave($data, $service = null)
    {
        $saved = parent::actionSave($data['operatingCentre'], 'OperatingCentre');

        if ($this->getActionName() == 'add') {
            if (!isset($saved['id'])) {
                throw new \Exception('Unable to save operating centre');
            }

            $data['applicationOperatingCentre']['operatingCentre'] = $saved['id'];
        }

        $saved = parent::actionSave($data['applicationOperatingCentre'], $service);

        if ($this->getActionName() == 'add' && !isset($saved['id'])) {
            throw new \Exception('Unable to save application operating centre');
        }
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
            $data['operatingCentre'] = $data['data']['operatingCentre'];
            $data['address'] = $data['operatingCentre']['address'];
            $data['address']['country'] = 'country.' . $data['address']['country'];
            unset($data['data']['operatingCentre']);
        }

        $data['data']['application'] = $this->getIdentifier();

        return $data;
    }

    /**
     * Process the loading of data
     *
     * @param array $data
     */
    protected function processLoad($oldData)
    {
        $results = $this->getTableData($this->getIdentifier());

        $data['data'] = $oldData;

        $data['data']['noOfOperatingCentres'] = count($results);
        $data['data']['minVehicleAuth'] = 0;
        $data['data']['maxVehicleAuth'] = 0;
        $data['data']['minTrailerAuth'] = 0;
        $data['data']['maxTrailerAuth'] = 0;
        $data['data']['licenceType'] = $this->getLicenceType();

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
}
