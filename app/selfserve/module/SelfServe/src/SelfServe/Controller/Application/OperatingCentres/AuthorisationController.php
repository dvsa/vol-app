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
     * Action data map
     *
     * @var array
     */
    protected $actionDataMap = array(
        '_addresses' => array(
            'address'
        ),
        'main' => array(
            'mapFrom' => array(
                'data'
            ),
            'children' => array(
                'addresses' => array(
                    'mapFrom' => array(
                        'addresses'
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

    /**
     * Holds the sub action service
     *
     * @var string
     */
    protected $actionService = 'ApplicationOperatingCentre';

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
        if ($this->isPsv()) {
            $form->get('data')->remove('totAuthTrailers');
            $form->get('data')->remove('minTrailerAuth');
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

        $data = $this->makeRestCall('ApplicationOperatingCentre', 'GET', array('application' => $id), $bundle);

        $newData = array();

        foreach ($data['Results'] as $row) {

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
     * Save the operating centre
     *
     * @param array $data
     */
    public function actionSave($data)
    {
        $saved = parent::actionSave($data);

        if (!isset($saved['id'])) {
            return $this->notFoundAction();
        }

        $data['operatingCentre'] = $saved['id'];

        $data['application'] = $this->getIdentifier();

        $this->makeRestCall('ApplicationOperatingCentre', 'POST', $data);
    }

    /**
     * Process the action load data
     *
     * @param array $data
     */
    protected function processActionLoad($data)
    {
        //$data = parent::processActionLoad($data);
        print '<pre>';
        print_r($data);
        print '</pre>';
        exit;

        return array('data' => $data);
    }
}
