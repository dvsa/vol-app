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
    
    public function indexAction() {
    
        // render the view
        $view = new ViewModel();
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

        //This block should be in db transaction
        try{
            $data = array(
                'name'      => '',
                'version'   => 1,
            );
            
            //create organisation
            $orgResult = $this->makeRestCall('Organisation', 'POST', $data);
            
            $data = array(
                'version'       => 1,
                'licenceNumber' => '',
                'licenceType'   => '',
                'licenceStatus' => 'lic_status.new',
                'organisation'  => $orgResult['id'],
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
        catch (\Exception $e){
            die('An error occured and transaction should be rolled back.');   
        }
    }

}
