<?php

/**
 * Payment Details Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Controller\PaymentDetails;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

/**
 * Payment Details Controller
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
        $view->setTemplate('self-serve/payment-details/index');

        return $view;
    }

    public function completeAction()
    {

    }
}
