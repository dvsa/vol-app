<?php

/**
 * Transport Managers Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Controller\TransportManagers;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

/**
 * Transport Managers Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class IndexController extends FormJourneyActionController
{
    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        // collect completion status
        $completionStatus = $this->makeRestCall('ApplicationCompletion', 'GET', array('application_id' => $applicationId));
        
        // render the view
        $view = new ViewModel(array('completionStatus' => $completionStatus['Results'][0],
                                            'applicationId' => $applicationId));
        $view->setTemplate('self-serve/transport-managers/index');

        return $view;
    }

    public function completeAction()
    {

    }
}
