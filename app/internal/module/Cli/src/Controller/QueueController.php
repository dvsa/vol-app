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
        // Which message type to process, if null then we process any message type
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
                sleep(1);
            } else {
                $this->getConsole()->writeLine($response);
            }
        }
    }

    /**
     * Decide whether to run again based on config settings and time elapsed
     *
     * @return boolean
     */
    protected function shouldRunAgain()
    {
        if (isset($this->endTime)) {
            return time() < $this->endTime;
        }

        return true;
    }
}
