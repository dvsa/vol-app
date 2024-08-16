<?php

/**
 * Queue Processor
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @note ported from olcs-internal Cli\Service\Queue
 */

namespace Dvsa\Olcs\Cli\Service\Queue;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Exception\ORMException;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Query\Queue\NextItem as NextQueueItemQry;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\MessageConsumerInterface;
use Olcs\Logging\Log\Logger;

/**
 * Queue Processor
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @note ported from olcs-internal Cli\Service\Queue
 */
class QueueProcessor
{
    /**
     * Create service instance
     *
     *
     * @return QueueProcessor
     */
    public function __construct(private readonly QueryHandlerManager $queryHandlerManager, private readonly MessageConsumerManager $messageConsumerManager)
    {
    }

    /**
     * Process next item
     *
     * @param array $includeTypes Types to include
     * @param array $excludeTypes Types to exclude
     *
     * @return string
     */
    public function processNextItem(array $includeTypes = [], $excludeTypes = [])
    {
        $item = $this->getNextItem($includeTypes, $excludeTypes);

        if ($item === null) {
            return null;
        }

        return $this->processMessage($item);
    }

    /**
     * Process message
     *
     * @param QueueEntity $item Queue item to process
     *
     * @return string
     */
    protected function processMessage($item)
    {
        $consumer = $this->getMessageConsumer($item);

        try {
            return $consumer->processMessage($item);
        } catch (ORMException $e) {
            Logger::logException($e, \Laminas\Log\Logger::ERR);
            // rethrow ORMException which can cause Entity Manager to close
            throw $e;
        } catch (DBALException $e) {
            Logger::logException($e, \Laminas\Log\Logger::ERR);
            // rethrow DBALException which can cause Entity Manager to close
            throw $e;
        } catch (\Exception $e) {
            Logger::logException($e, \Laminas\Log\Logger::ERR);
            return $consumer->failed($item, $e->getMessage());
        }
    }

    /**
     * Grab the next message in the queue
     *
     * @param array $includeTypes Types to include
     * @param array $excludeTypes Types to exclude
     *
     * @return QueueEntity|null
     */
    protected function getNextItem(array $includeTypes, array $excludeTypes)
    {
        $query = NextQueueItemQry::create(['includeTypes' => $includeTypes, 'excludeTypes' => $excludeTypes]);
        return $this->queryHandlerManager->handleQuery($query);
    }

    /**
     * Get message consumer
     *
     * @param QueueEntity $item Queue item
     *
     * @return MessageConsumerInterface
     */
    protected function getMessageConsumer($item)
    {
        return $this->messageConsumerManager->get($item->getType()->getId());
    }
}
