<?php

/**
 * Continuation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;

/**
 * Continuation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationController extends AbstractController
{
    public function indexAction()
    {
        $form = $this->getContinuationForm();

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        $this->setNavigationId('admin-dashboard/continuations');

        $this->getServiceLocator()->get('Script')->loadFile('continuations');

        return $this->renderView($view, 'admin-generate-continuations-title');
    }

    protected function getContinuationForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('GenerateContinuation');
    }
}
