<?php

/**
 * Transport Manager Case Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\TransportManager\TransportManagerController;
use Olcs\Controller\Traits;

/**
 * Transport Manager Case Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerCaseController extends TransportManagerController
{
    /**
     * @var string
     */
    protected $section = 'cases';

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithTM();
        $view->setTemplate('transport-manager/index');
        return $this->renderView($view);
    }
}