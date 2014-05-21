<?php

/**
 * Index Controller (Dashboard)
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Dashboard;

use SelfServe\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Common\Exception\ResourceNotFoundException;
use Zend\Http\Response;

/**
 * Class IndexController
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexController extends AbstractController
{

    /**
     * Holds the applications bundle
     *
     * @var array
     */
    private $applicationsBundle = array(
        'properties' => array(),
        'children' => array(
            'organisation' => array(
                'properties' => array(),
                'children' => array(
                    'licences' => array(
                        'properties' => array(
                            'licenceNumber'
                        ),
                        'children' => array(
                            'applications' => array(
                                'properties' => array(
                                    'id',
                                    'createdOn',
                                    'receivedDate',
                                    'status'
                                )
                            )
                        )
                    )
                )
            )
        )
    );

    /**
     * Holds the organisation bundle
     *
     * @var array
     */
    private $organisationIdBundle = array(
        'properties' => array(

        ),
        'children' => array(
            'organisation' => array(
                'properties' => array('id')
            )
        )
    );

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $user = $this->getUser();

        $data = $this->makeRestCall('User', 'GET', array('id' => $user['id']), $this->applicationsBundle);

        $applications = array();

        if (isset($data['organisation']['licences'])) {
            foreach ($data['organisation']['licences'] as $licence) {
                foreach ($licence['applications'] as $application) {
                    $newRow = $application;
                    $newRow['licenceNumber'] = $licence['licenceNumber'];
                    $applications[] = $newRow;
                }
            }
        }

        $settings = array(
            'sort' => 'createdOn',
            'order' => 'DESC',
            'limit' => 10,
            'page' => 1
        );

        $applicationsTable = $this->buildTable('dashboard-applications', $applications, $settings);

        $view = $this->getViewModel(['applicationsTable' => $applicationsTable]);
        $view->setTemplate('self-serve/dashboard/index');

        return $view;
    }

    /**
     * Method to add the required database entries and redirect to beginning
     * of the application journey.
     *
     * @return Response
     */
    public function createAction()
    {
        $user = $this->getUser();

        $data = [
            'version'       => 1,
            'licenceNumber' => '',
            'licenceType'   => '',
            'licenceStatus' => 'lic_status.new',
            'organisation'  => $this->getOrganisationId($user['id']),
        ];

        $licenceResult = $this->makeRestCall('Licence', 'POST', $data);
        $licenceId = $licenceResult['id'];

        $data = [
            'licence' => $licenceId,
            'createdOn'   => date('Y-m-d h:i:s'),
            'status' => 'app_status.new'
        ];

        $applicationResult = $this->makeRestCall('Application', 'POST', $data);
        $applicationId = $applicationResult['id'];

        $data = [
            'application' => $applicationId,
        ];

        $this->makeRestCall('ApplicationCompletion', 'POST', $data);

        return $this->redirectToRoute('Application', ['applicationId' => $applicationId]);
    }

    /**
     * Get organisation Id based on current user
     *
     * @throws \Exception
     * @return int
     */
    private function getOrganisationId($userId)
    {
        $user = $this->makeRestCall('User', 'GET', ['id' => $userId], $this->organisationIdBundle);

        return $user['organisation']['id'];
    }

    /**
     * Currently there is no authentication mechanism, so userId is retrieved from route param
     *
     * @return array|Response
     */
    private function getUser()
    {
        return array('id' => 1);
    }
}
