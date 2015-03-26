<?php

/**
 * Split Screen Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Split Screen Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SplitScreenController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('layout/split-screen');
        $view->setTerminal(true);

        $this->getServiceLocator()->get('Script')->loadFile('split-screen');

        return $view;
    }
}
