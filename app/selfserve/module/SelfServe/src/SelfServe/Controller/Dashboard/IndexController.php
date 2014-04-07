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

        $data['version'] = 1;
        $data['licenceNumber'] = '';
        $data['licenceType'] = '';
        $data['licenceStatus'] = 'lic_status.new';

        // create licence
        $result = $this->makeRestCall('Licence', 'POST', $data);
        $licenceId = $result['id'];
                
        $this->redirect()->toRoute('selfserve/licence-type', array('licenceId' => $licenceId, 'step' => 'operator-location'));
        
    }

}
