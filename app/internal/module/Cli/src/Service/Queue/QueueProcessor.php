<?php

/**
 * Queue Processor
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\Service\Queue;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Queue Processor
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QueueProcessor implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function processNextItem($type = null)
    {
        $item = $this->getNextItem($type);

        if ($item === null) {
            return null;
        }

        try {
            if ($this->processMessage($item)) {
                return $this->processSuccess($item);
            }

            $ex = null;
        } catch (\Exception $ex) {
            // Catch uncaught exceptions, and pass them to the processFailure method
        }

        return $this->processFailure($item, $ex);
    }

    protected function processMessage($item)
    {
        $consumer = $this->getMessageConsumer($item);
        return $consumer->processMessage($item);
    }

    protected function processSuccess($item)
    {
        $consumer = $this->getMessageConsumer($item);
        return $consumer->processSuccess($item);
    }

    protected function processFailure($item, $ex = null)
    {
        $consumer = $this->getMessageConsumer($item);
        return $consumer->processFailure($item, $ex);
    }

    /**
     * Grab the next message in the queue
     *
     * @param string $type
     * @return array
     */
    protected function getNextItem($type = null)
    {
        return $this->getServiceLocator()->get('Entity\Queue')->getNextItem($type);
    }

    protected function getMessageConsumer($item)
    {
        return $this->getServiceLocator()->get('MessageConsumerManager')
            ->get($item['type']['id']);
    }

    protected function formatOptions($options)
    {
        if (empty($options)) {
            return [];
        }

        $decodedOptions = json_decode($options, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decodedOptions;
        }

        return [];
    }
}
