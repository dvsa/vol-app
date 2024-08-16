<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\Exception\ORMException;
use Dvsa\Olcs\Api\Domain\Exception\Exception as DomainException;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Domain\Exception\NysiisException;
use Olcs\Logging\Log\Logger;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Laminas\ServiceManager\Exception\ExceptionInterface as LaminasServiceException;

/**
 * Abstract Command Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractCommandConsumer extends AbstractConsumer
{
    /**
     * @var int Max retry attempts before fails
     */
    protected $maxAttempts = 100;

    /**
     * @var int Retry internal in seconds
     */
    protected $retryAfter = 900;

    /**
     * @var string the command to handle processing
     */
    protected $commandName = 'override_me';

    /**
     * Gets the command data
     *
     * @param QueueEntity $item the queue item
     *
     * @return array
     */
    abstract public function getCommandData(QueueEntity $item);

    /**
     * Gets the command name
     *
     * @param QueueEntity $item the queue item
     *
     * @return string
     */
    protected function getCommandName(QueueEntity $item)
    {
        return $this->commandName;
    }

    /**
     * Process the message item
     *
     * @param QueueEntity $item the queue item
     *
     * @return string
     */
    public function processMessage(QueueEntity $item)
    {
        if (!empty($this->maxAttempts) && $item->getAttempts() > $this->maxAttempts) {
            return $this->failed($item, QueueEntity::ERR_MAX_ATTEMPTS);
        }

        $commandClass = $this->getCommandName($item);
        $commandData = $this->getCommandData($item);
        $command = $commandClass::create($commandData);

        try {
            $result = $this->handleCommand($command);
        } catch (NotReadyException | NysiisException $e) {
            Logger::logException($e, \Laminas\Log\Logger::WARN);
            return $this->retry($item, $e->getRetryAfter(), $e->getMessage());
        } catch (EmailNotSentException $e) {
            Logger::logException($e, \Laminas\Log\Logger::WARN);
            return $this->retry($item, $this->retryAfter, $e->getMessage());
        } catch (DomainException $e) {
            Logger::logException($e, \Laminas\Log\Logger::ERR);
            $message = !empty($e->getMessages()) ? implode(', ', $e->getMessages()) : $e->getMessage();
            return $this->failed($item, $message);
        } catch (LaminasServiceException $e) {
            Logger::logException($e, \Laminas\Log\Logger::ERR);
            return $this->handleLaminasServiceException($item, $e);
        } catch (ORMException | DBALException | \Exception $e) {
            Logger::logException($e, \Laminas\Log\Logger::ERR);
            return $this->failed($item, $e->getMessage());
        }

        $message = null;
        if (!empty($result->getMessages())) {
            $message = implode(', ', $result->getMessages());
        }
        return $this->success($item, $message);
    }

    /**
     * Method to handle the Service Manager exception. Default to failed.
     *
     * @param QueueEntity $item queue item
     * @param \Exception  $e    exception
     *
     * @return string
     */
    protected function handleLaminasServiceException(QueueEntity $item, \Exception $e)
    {
        return $this->failed($item, $e->getMessage());
    }
}
