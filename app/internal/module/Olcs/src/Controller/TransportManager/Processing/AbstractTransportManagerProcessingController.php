<?php

namespace Olcs\Controller\TransportManager\Processing;

use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\TransportManager\TransportManagerController;

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
