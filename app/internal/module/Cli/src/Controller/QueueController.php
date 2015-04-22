<?php

/**
 * Queue Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;

/**
 * Queue Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

class QueueController extends AbstractConsoleController
{
    protected $start;

    protected $maxRunningTime = 50;

    public function indexAction()
    {
        $this->start = time();
        $endTime = $this->start + $this->maxRunningTime;

        $service = $this->getServiceLocator()->get('Queue');

        while (time() < $endTime) {

            $response = $service->processNextItem();

            if ($response === null) {
                $this->getConsole()->writeLine('No items queued, waiting for items');
                sleep(2);
            } else {
                $this->getConsole()->writeLine($response);
            }
        }
    }
}
