<?php

/**
 * Transport Manager Processing Decision Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Processing;

/**
 * Transport Manager Processing Decision Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerProcessingDecisionController extends AbstractTransportManagerProcessingController
{
    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithTm();
        $view->setTemplate('pages/placeholder');

        return $this->renderView($view);
    }
}
