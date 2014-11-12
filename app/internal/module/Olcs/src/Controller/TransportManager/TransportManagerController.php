<?php

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\AbstractController;

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerController extends AbstractController
{
    /**
     * @var string
     */
    protected $pageLayout = 'transport-manager';

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('transport-manager/details/details', [], [], true);
    }

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexProcessingJumpAction()
    {
        return $this->redirect()->toRoute('transport-manager/processing/notes', [], [], true);
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
