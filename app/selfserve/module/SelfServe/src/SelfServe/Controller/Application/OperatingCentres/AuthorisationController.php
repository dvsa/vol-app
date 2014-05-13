<?php

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\OperatingCentres;

use Zend\Http\Response;

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthorisationController extends OperatingCentresController
{
    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        $action = $this->checkForCrudAction();

        if ($action instanceof Response) {
            return $action;
        }

        $bundle = array(
            'properties' => array(
                'version',
                'totAuthVehicles',
                'totAuthTrailers'
            )
        );

        $data = $this->makeRestCall('Application', 'GET', array('id' => $this->getIdentifier()), $bundle);

        if (empty($data)) {
            return $this->render($this->notFoundAction());
        }

        $results = $this->getOperatingCentresForApplication($this->getIdentifier());

        $table = $this->getOperatingCentreTable($results);

        $view = $this->getViewModel(array('table' => $table));

        return $this->renderSection($view);
    }

    /**
     * Save data
     *
     * @param array $data
     */
    public function save($data)
    {
    }

    /**
     * Load data from id
     *
     * @param int $id
     */
    public function load($id)
    {
        return array('data' => array());
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
            'page' => 1
        );

        return $this->buildTable(
            'operatingcentre',
            $results,
            $settings
        );
    }
}
