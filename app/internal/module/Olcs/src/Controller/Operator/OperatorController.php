<?php

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

use Olcs\Controller\AbstractController;

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorController extends AbstractController
{
    /**
     * @var string
     */
    protected $pageLayout = 'operator';

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('operator/busines-details', [], [], true);
    }
    
    public function businessDetailsAction()
    {
        $view = $this->getViewWithTm();
        $view->setTemplate('operator/index');
        return $this->renderView($view);
    }

    public function peopleAction()
    {
        $view = $this->getViewWithTm();
        $view->setTemplate('operator/index');
        return $this->renderView($view);
    }

    public function licenceApplicationAction()
    {
        $view = $this->getViewWithTm();
        $view->setTemplate('operator/index');
        return $this->renderView($view);
    }

    /**
     * Get view with TM
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithTm($variables = [])
    {
        // implement later
        $transportManager = null;

        $variables['transportManager'] = $transportManager;
        $variables['section'] = $this->section;

        $view = $this->getView($variables);

        // implement later
        $this->pageTitle = 'Dave Watson';

        return $view;
    }
}