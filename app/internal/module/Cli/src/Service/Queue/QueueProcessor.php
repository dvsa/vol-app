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

        return $this->processMessage($item);
    }

    /**
     * Can be called from within a consumer to format a message's options
     *
     * @param string $options
     * @return array
     */
    public function formatOptions($options)
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

    protected function processMessage($item)
    {
        $consumer = $this->getMessageConsumer($item);
        return $consumer->processMessage($item);
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
}
