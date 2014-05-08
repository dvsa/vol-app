<?php

/**
 * Payment Details Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */

namespace SelfServe\Controller\PaymentDetails;

use SelfServe\Controller\AbstractApplicationController;
use Zend\View\Model\ViewModel;

/**
 * Payment Details Controller
 *
 * @author Jess Rowbottom <jess.rowbottom@valtech.co.uk>
 */
class IndexController extends AbstractApplicationController
{
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->processPayment();
        }

        $form = $this->generateForm('payment', 'processPayment');

        $applicationId = $this->getApplicationId();

        $view = $this->getViewModel(array('form' => $form));

        $view->setTemplate('self-serve/layout/form');

        return $this->renderLayoutWithSubSections($view);
    }

    public function processPayment()
    {
        $applicationId = $this->getApplicationId();

        return $this->redirectToRoute('selfserve/payment-submission', array('applicationId' => $applicationId));
    }

    public function completeAction()
    {
        $view = new ViewModel();
        $view->setTemplate('self-serve/payment-details/complete');
        return $view;
    }
}
