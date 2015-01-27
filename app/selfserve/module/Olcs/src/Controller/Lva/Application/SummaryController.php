<?php

/**
 * External Application Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Zend\View\Model\ViewModel;

/**
 * External Application Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryController extends AbstractController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = (array)$this->getRequest()->getPost();

            if (isset($data['submitDashboard'])) {
                return $this->redirect()->toRoute('dashboard');
            }

            // otherwise just assume we want to view our application summary
            // (actually the Overview page)
            return $this->redirectToOverview();
        }
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Lva\PaymentSummary');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('summary-application');

        return $this->render($view);
    }
}
