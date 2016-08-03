<?php

namespace Cli\Service\Queue\Consumer\CompaniesHouse;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Cli\Service\Queue\Consumer\MessageConsumerInterface;

/**
 * Abstract Companies House Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractConsumer implements MessageConsumerInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var string the Business Service class to handle processing
     */
    protected $businessServiceName;

    /**
     * Process the message item
     *
     * @param array $item Message item
     *
     * @return boolean
     */
    public function processMessage(array $item)
    {
        $options = (array) json_decode($item['options']);

        /** @var \Common\BusinessService\Response $response */
        $response = $this->getServiceLocator()->get('BusinessServiceManager')
            ->get($this->businessServiceName)
            ->process(['companyNumber' => $options['companyNumber']]);

        if (!$response->isOk()) {
            return $this->failed($item, $response->getMessage());
        }

        return $this->success($item, $response->getMessage());
    }

    /**
     * Called when processing the message was successful
     *
     * @param array       $item    Message item
     * @param string|null $message Description
     *
     * @return string
     */
    protected function success(array $item, $message = null)
    {
        $this->getServiceLocator()->get('Entity\Queue')->complete($item);

        return 'Successfully processed message: '
            . $item['id'] . ' ' . $item['options']
            . ($message ? ' ' . $message : '');
    }

    /**
     * Mark the message as failed
     *
     * @param array  $item   Message item
     * @param string $reason Description
     *
     * @return string
     */
    protected function failed(array $item, $reason = null)
    {
        $this->getServiceLocator()->get('Entity\Queue')->failed($item);

        return 'Failed to process message: '
            . $item['id'] . ' ' . $item['options']
            . ' ' .  $reason;
    }
}
