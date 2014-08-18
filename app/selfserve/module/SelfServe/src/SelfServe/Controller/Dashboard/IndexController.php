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
            'organisationUsers' => array(
                'properties' => null,
                'children' => array(
                    'organisation' => array(
                        'properties' => array(),
                        'children' => array(
                            'licences' => array(
                                'properties' => array(
                                    'licNo'
                                ),
                                'children' => array(
                                    'applications' => array(
                                        'properties' => array(
                                            'id',
                                            'createdOn',
                                            'receivedDate'
                                        ),
                                        'children' => array(
                                            'status' => array(
                                                'properties' => array(
                                                    'id'
                                                )
                                            )
                                        )
                                    )
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
    private $organisationUserBundle = array(
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

        if (isset($data['organisationUsers'])) {
            foreach ($data['organisationUsers'] as $orgUser) {
                foreach ($orgUser['organisation']['licences'] as $licence) {
                    foreach ($licence['applications'] as $application) {
                        $newRow = $application;
                        $newRow['licNo'] = $licence['licNo'];
                        $newRow['status'] = $application['status']['id'];
                        $applications[$newRow['id']] = $newRow;
                    }
                }
            }
        }

        ksort($applications);

        $applications = array_reverse($applications);

        $applicationsTable = $this->buildTable('dashboard-applications', $applications);

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
            'version' => 1,
            'licNo' => '',
            'status' => 'lsts_new',
            'organisation' => $this->getOrganisationId($user['id']),
        ];

        $licenceResult = $this->makeRestCall('Licence', 'POST', $data);
        $licenceId = $licenceResult['id'];

        $data = [
            'licence' => $licenceId,
            'createdOn' => date('Y-m-d h:i:s'),
            'status' => 'apsts_new'
        ];

        $applicationResult = $this->makeRestCall('Application', 'POST', $data);
        $applicationId = $applicationResult['id'];

        $data = [
            'id' => $applicationId,
        ];

        $this->makeRestCall('ApplicationCompletion', 'POST', $data);

        return $this->redirectToRoute('Application', ['applicationId' => $applicationId]);
    }

    /**
     * Get organisation Id based on current user
     * @IMPORTANT User's can now be linked to more than 1 organisation, in the future there will be a dropdown for these
     * users to select which organisation they are dealing with, which will be stored in session so we will need @todo
     * some changes at that stage, for now we just grab the first organisation
     *
     * @throws \Exception
     * @return int
     */
    private function getOrganisationId($userId)
    {
        $organisation = $this->makeRestCall('OrganisationUser', 'GET', ['user' => $userId], $this->organisationUserBundle);

        if ($organisation['Count'] < 1) {
            return null;
        }

        return $organisation['Results'][0]['organisation']['id'];
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
