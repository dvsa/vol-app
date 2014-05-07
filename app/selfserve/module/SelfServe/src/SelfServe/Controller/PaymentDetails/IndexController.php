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
    /**
     * @todo When OLCS-2392 is merged, ensure the HTML elements are rendered correctly
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->processPayment();
        }

        $form = $this->generateForm('payment', 'processPayment');

        $applicationId = $this->params()->fromRoute('applicationId');

        // collect completion status
        $completionStatus = $this->makeRestCall('ApplicationCompletion', 'GET', array('application_id' => $applicationId));

        // render the view
        $view = $this->getViewModel(
            array(
                'form' => $form,
                'completionStatus' => $completionStatus['Results'][0],
                'applicationId' => $applicationId
            )
        );

        $view->setTemplate('self-serve/forms/generic');

        return $view;
    }

    public function processPayment($data)
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        return $this->redirect()->toRoute('selfserve/summary-complete', array('applicationId' => $applicationId));
    }

    public function completeAction()
    {

    }
}
