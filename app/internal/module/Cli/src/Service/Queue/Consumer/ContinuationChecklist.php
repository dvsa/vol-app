<?php

/**
 * Continuation Checklist
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Cli\Service\Queue\Consumer;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\BusinessService\Response;

/**
 * Continuation Checklist
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationChecklist implements MessageConsumerInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Process the message item
     *
     * @param array $item
     * @return boolean
     */
    public function processMessage(array $item)
    {
        $response = $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('Cli\ContinuationDetail')
            ->process(['id' => $item['entityId']]);

        if ($response->getType() === Response::TYPE_NO_OP) {
            return $this->skip($item);
        }

        if ($response->getType() === Response::TYPE_FAILED) {
            return $this->failed($item, $response->getMessage());
        }

        return $this->success($item);
    }

    protected function skip(array $item)
    {
        $this->getServiceLocator()->get('Entity\Queue')->complete($item);

        return 'Continuation detail no longer pending';
    }

    /**
     * Called when processing the message was successful
     *
     * @param array $item
     * @return string
     */
    protected function success(array $item, $message = 'Successful')
    {
        $this->getServiceLocator()->get('Entity\Queue')->complete($item);

        return $message;
    }

    /**
     * Mark the message as failed and continuation detail record as errored
     *
     * @param array $item
     * @param string $reason
     * @return string
     */
    protected function failed(array $item, $reason = null)
    {
        $this->getServiceLocator()->get('Entity\Queue')->failed($item);
        $this->getServiceLocator()->get('Entity\ContinuationDetail')->checklistFailed($item['entityId']);

        return $reason;
    }
}
