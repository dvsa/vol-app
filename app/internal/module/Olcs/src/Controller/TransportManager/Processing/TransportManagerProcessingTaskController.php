<?php

/**
 * Transport Manager Processing Task Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\TransportManager\Processing\AbstractTransportManagerProcessingController;
use Zend\Navigation\Navigation;

/**
 * Transport Manager Processing Task Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerProcessingTaskController extends AbstractTransportManagerProcessingController
{
    /**
     * @var string
     */
    protected $section = 'processing-tasks';

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