<?php

namespace Dvsa\Olcs\Cli\Domain\Command\MessageQueue;

use Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\CompanyProfile;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

class Enqueue extends AbstractCommand implements LoggerOmitContentInterface
{
    protected $messageData;

    #[Transfer\Validator("Laminas\Validator\InArray", options: ["haystack" => [CompanyProfile::class]])]
    protected $messageType;

    #[Transfer\Validator("Laminas\Validator\InArray", options: ["haystack" => [CompanyProfile::class]])]
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
