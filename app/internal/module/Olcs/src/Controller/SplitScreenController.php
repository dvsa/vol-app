<?php

/**
 * Split Screen Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller;

use Common\Service\Script\ScriptFactory;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class SplitScreenController extends AbstractActionController
{
    protected ScriptFactory $scriptFactory;

    public function __construct(ScriptFactory $scriptFactory)
    {
        $this->scriptFactory = $scriptFactory;
    }

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('layout/split-screen');
        $view->setTerminal(true);

        $this->scriptFactory->loadFile('split-screen');

        return $view;
    }
}
