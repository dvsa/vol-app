<?php

/**
 * Transport Manager Processing History Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\TransportManager\Processing\AbstractTransportManagerProcessingController;
use Zend\Navigation\Navigation;

/**
 * Transport Manager Processing History Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerProcessingHistoryController extends AbstractTransportManagerProcessingController
{
    /**
     * @var string
     */
    protected $section = 'processing-history';

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithTm();
        $view->setTemplate('transport-manager/index');
        return $this->renderView($view);
    }
}
