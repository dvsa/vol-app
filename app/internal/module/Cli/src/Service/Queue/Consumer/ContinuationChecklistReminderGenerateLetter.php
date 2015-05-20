<?php

/**
 * CContinuationChecklistGenerateLetters
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Cli\Service\Queue\Consumer;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\BusinessService\Response;

/**
 * ContinuationChecklistGenerateLetters
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationChecklistReminderGenerateLetter implements MessageConsumerInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Process the message item
     *
     * @param array $item Queue entity data
     *
     * @return string
     */
    public function processMessage(array $item)
    {
        $response = $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('ContinuationChecklistReminderGenerateLetter')
            ->process(['continuationDetailId' => $item['entityId']]);

        if (!$response->isOk()) {
            return $this->failed($item, $response->getMessage());
        }

        return $this->success($item);
    }

    /**
     * Mark the message as successful
     *
     * @param array $item Queue entity data
     *
     * @return string Message
     */
    protected function success(array $item)
    {
        $this->getServiceLocator()->get('Entity\Queue')->complete($item);

        return sprintf('[%s] Success [queue.id:%d]', __CLASS__, $item['id']);
    }

    /**
     * Mark the message as failed
     *
     * @param array $item Queue entity data
     *
     * @return string Message
     */
    protected function failed(array $item, $reason)
    {
        $this->getServiceLocator()->get('Entity\Queue')->failed($item);

        return sprintf('[%s] Failed [queue.id:%d] %s', __CLASS__, $item['id'], $reason);
    }
}
