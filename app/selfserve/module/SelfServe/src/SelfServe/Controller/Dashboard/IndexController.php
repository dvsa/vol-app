<?php

/**
 * Index Controller. Used to generate a static page which is where the user 
 * journey will begin.
 * This page essentially sets up all the required database entries and redirects
 * the user to the route
 *
 * @package    Selfserve
 * @subpackage Dashboard
 * @author     S Lizzio <shaun.lizzio@valtech.co.uk>
 * @author     Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace SelfServe\Controller\Dashboard;

use SelfServe\Controller\AbstractApplicationController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

/**
 * Class IndexController
 *
 * @package SelfServe
 * @author  Jakub Igla <jakub.igla@valtech.co.uk>
 */
class IndexController extends AbstractApplicationController
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
        if ($user instanceof \Zend\Http\Response) {
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
            'page' => 1,
            'url' => $this->getPluginManager()->get('url')
        );

        $applicationsTable = $this->getServiceLocator()->get('Table')->buildTable('dashboard-applications', $applications, $settings);

        //\Zend\Debug\Debug::dump($applications);exit;

        // render the view
        $view = new ViewModel(['applicationsTable' => $applicationsTable]);
        $view->setTemplate('self-serve/dashboard/index');
        return $view;
    }

    public function determineSectionAction()
    {
        $applicationId = $this->getApplicationId();
        $journeySections = $this->getServiceLocator()->get('config')['journey'];

        $applicationCompletionResult = $this->makeRestCall('ApplicationCompletion', 'GET', ['application' => $applicationId]);

        if ($applicationCompletionResult['Count'] == 0) {
            throw new \Common\Exception\ResourceNotFoundException('No entity found');
        }
        $applicationCompletion = $applicationCompletionResult['Results'][0];

        $section = empty($applicationCompletion['lastSection'])
            ? current($journeySections)
            : $journeySections[$applicationCompletion['lastSection']];

        return $this->redirect()->toRoute(
            'selfserve/' . $section['route'],
            ['step' => $section['step'], 'applicationId' => $applicationId]
        );
    }


    /**
     * Method to add the required database entries and redirect to beginning 
     * of the application journey. 
     *
     * @return \Zend\Http\Response
     */
    public function createApplicationAction()
    {

        $user = $this->getUser();
        if ($user instanceof \Zend\Http\Response) {
            return $user;
        }

        $data = [
            'version'       => 1,
            'licenceNumber' => '',
            'licenceType'   => '',
            'licenceStatus' => 'lic_status.new',
            'organisation'  => $this->getOrganisationId(),
        ];

        // create licence
        $licenceResult = $this->makeRestCall('Licence', 'POST', $data);
        $licenceId = $licenceResult['id'];

        $data = [
            'version'       => 1,
            'licence' => $licenceId,
            'createdOn'   => date('Y-m-d h:i:s'),
            'status' => 'app_status.new'
        ];

        // create application
        $applicationResult = $this->makeRestCall('Application', 'POST', $data);
        $applicationId = $applicationResult['id'];

        $data = [
            'version' => 1,
            'application' => $applicationId,
        ];

        $this->makeRestCall('ApplicationCompletion', 'POST', $data);

        return $this->redirect()->toRoute(
            'selfserve/licence-type',
            [
                'applicationId' => $applicationId,
                'step' => 'operator-location',
            ]
        );
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
            throw new \Exception('User not found');
        }
        return $user['organisation']['id'];
    }

    /**
     * Currently there is no authentication mechanism, so userId is retrieved from route param
     *
     * @return array|\Zend\Http\Response
     */
    private function getUser()
    {
        if (empty($this->user)) {
            $userId = $this->params()->fromRoute('userId');
            $session = new Container();

            if (empty($userId)) {

                if (empty($session->user)) {
                    // redirect to temp user
                    return $this->redirect()->toRoute('selfserve/dashboard-home', ['userId' => 1]);
                }

                $this->user = $session->user;

            } else {
                $session->user = $this->user = array('id' => $userId);
            }
        }
        return $this->user;
    }

}
