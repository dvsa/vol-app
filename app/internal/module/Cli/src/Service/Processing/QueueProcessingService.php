<?php

/**
 * Queue Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\Service\Processing;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Service\Entity\QueueEntityService;

/**
 * Queue Processing Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QueueProcessingService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function processNextItem()
    {
        $item = $this->getNextItem();

        if ($item === null) {
            return null;
        }

        return $this->processItem($item['type']['id'], $item['entityId'], $this->formatOptions($item['options']));
    }

    protected function processItem($type, $entityId, $options)
    {
        if ($type === QueueEntityService::TYPE_SLEEP) {
            sleep($options['time']);
            return 'Just slept for ' . $options['time'] . ' seconds';
        }
    }

    protected function getNextItem()
    {
        return $this->getServiceLocator()->get('Entity\Queue')->getNextItem();
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
