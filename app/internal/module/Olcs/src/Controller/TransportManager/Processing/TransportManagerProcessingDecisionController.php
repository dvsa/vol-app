<?php

/**
 * Transport Manager Processing Decision Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\TransportManager\Processing\AbstractTransportManagerProcessingController;

/**
 * Transport Manager Processing Decision Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerProcessingDecisionController extends AbstractTransportManagerProcessingController
{
    /**
     * @var string
     */
    protected $section = 'processing-decisions';

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithTm();
        $view->setTemplate('view-new/pages/placeholder');
        return $this->renderView($view);
    }
}
