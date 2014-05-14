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
     * Holds the sub action service
     *
     * @var string
     */
    protected $subActionService = 'ApplicationOperatingCentre';

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
     * Load data for the sub section
     *
     * @param int $id
     * @return array
     */
    protected function loadSubSection($id)
    {
        
    }

    protected function saveSubAction($data)
    {
        $id = $this->getSubActionId();

        $data = $data['data'];

        if (!empty($id)) {
            $data['id'] = $id;
        }

        $this->makeRestCall('ApplicationOperatingCentre', 'PUT', $data);
    }

    /**
     * Save data
     *
     * @param array $data
     */
    protected function save($data)
    {
    }

    /**
     * Load data from id
     *
     * @param int $id
     */
    protected function load($id)
    {
        return array('data' => array());
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
}
