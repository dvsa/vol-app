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
    protected $startTime;
    protected $endTime;

    public function indexAction()
    {
        $type = $this->getRequest()->getParam('type');
        $config = $this->getServiceLocator()->get('Config')['queue'];

        $service = $this->getServiceLocator()->get('Queue');

        // Then we need to run for a given length of time
        if (empty($config['isLongRunningProcess'])) {
            $this->startTime = time();
            $this->endTime = $this->startTime + $config['runFor'];
        }

        while ($this->shouldRunAgain()) {

            $response = $service->processNextItem($type);

            if ($response === null) {
                $this->getConsole()->writeLine('No items queued, waiting for items');
                sleep(2);
            } else {
                $this->getConsole()->writeLine($response);
            }
        }
    }

    protected function shouldRunAgain()
    {
        if (isset($this->endTime)) {
            return time() < $this->endTime;
        }

        return true;
    }
}
