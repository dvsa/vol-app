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
     * User
     *
     * @var array
     */
    private $user;

    /**
     * Index action
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $user = $this->getUser();

        if ($user instanceof Response) {
            return $user;
        }

        $applications = $this->makeRestCall(
            'OrganisationApplication',
            'GET',
            ['organisation' => $this->getOrganisationId()],
            ['children' => ['licence']]
        );

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
    public function createApplicationAction()
    {
        $user = $this->getUser();

        if ($user instanceof Response) {
            return $user;
        }

        $data = [
            'version'       => 1,
            'licenceNumber' => '',
            'licenceType'   => '',
            'licenceStatus' => 'lic_status.new',
            'organisation'  => $this->getOrganisationId(),
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
    private function getOrganisationId()
    {
        $restBundle = ['children' => ['organisation']];
        $user = $this->makeRestCall('User', 'GET', ['id' => $this->user['id']], $restBundle);

        if ($user === false) {
            throw new ResourceNotFoundException('User not found');
        }
        return $user['organisation']['id'];
    }

    /**
     * Currently there is no authentication mechanism, so userId is retrieved from route param
     *
     * @return array|Response
     */
    private function getUser()
    {
        if (empty($this->user)) {

            $userId = $this->params()->fromRoute('userId');
            $session = new Container();

            if (empty($userId)) {

                if (empty($session->user)) {

                    // @todo redirect to temp user
                    return $this->redirectToRoute('home/dashboard', ['userId' => 1]);
                }

                $this->user = $session->user;

            } else {
                $session->user = $this->user = array('id' => $userId);
            }
        }
        return $this->user;
    }
}
