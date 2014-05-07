<?php

/**
 * Index Controller. Used to generate a static page which is where the user 
 * journey will begin.
 * This page essentially sets up all the required database entries and redirects
 * the user to the route
 *
 *
 * @package		selfserve
 * @subpackage          index
 * @author		S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace SelfServe\Controller\Dashboard;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

class IndexController extends FormActionController
{
    
    public function indexAction()
    {
        //hardcoded organisationId
        $organisationId = $this->getOrganisationId();

        $applications = $this->makeRestCall('OrganisationApplication',
            'GET',
            ['operatorId' => $organisationId],
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
    
		
    /**
     * Method to add the required database entries and redirect to beginning 
     * of the application journey. 
     * 
     */
    public function createApplicationAction()
    {

        $data = array(
            'version'       => 1,
            'licenceNumber' => '',
            'licenceType'   => '',
            'licenceStatus' => 'lic_status.new',
            'organisation'  => $this->getOrganisationId(),
        );

        // create licence
        $licenceResult = $this->makeRestCall('Licence', 'POST', $data);
        $licenceId = $licenceResult['id'];

        $data = array(
            'version'       => 1,
            'licence' => $licenceId,
            'createdOn'   => date('Y-m-d h:i:s'),
            'status' => 'app_status.new'
        );

        // create application
        $applicationResult = $this->makeRestCall('Application', 'POST', $data);
        $applicationId = $applicationResult['id'];

        $this->redirect()->toRoute('selfserve/licence-type', array('applicationId' => $applicationId, 'step' => 'operator-location'));

    }

    /**
     * Get organisation Id (currently hardcoded)
     *
     * @return int
     */
    private function getOrganisationId()
    {
        return 104;
    }

}
