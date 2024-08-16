<?php

namespace Dvsa\Olcs\Cli\Domain\Command\MessageQueue;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;

class Enqueue extends AbstractCommand implements LoggerOmitContentInterface
{
    protected $messageData;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\CompanyProfile"}})
     */
    protected $messageType;

    /**
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\CompanyProfile"}})
     */
    protected $queueType;

    /**
     * @return array
     */
    public function getMessageData(): array
    {
        return $this->messageData;
    }

    /**
     * @return string
     */
    public function getQueueType(): string
    {
        return $this->queueType;
    }

    /**
     * @return mixed
     */
    public function getMessageType(): string
    {
        return $this->messageType;
    }
}
