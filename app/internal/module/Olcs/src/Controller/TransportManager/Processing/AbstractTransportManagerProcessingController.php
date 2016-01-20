<?php

/**
 * Abstract Transport Manager Processing Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Processing;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\TransportManager\TransportManagerController;
use Zend\View\Model\ViewModel;

/**
 * Abstract Transport Manager Processing Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractTransportManagerProcessingController extends TransportManagerController implements
    LeftViewProvider
{
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }
}
