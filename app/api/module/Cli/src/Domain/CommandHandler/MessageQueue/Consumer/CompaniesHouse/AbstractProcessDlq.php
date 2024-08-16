<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\Email\SendFailedOrganisationsList;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\ConfigAwareInterface;
use Dvsa\Olcs\Api\Domain\ConfigAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\MessageFailures;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\AbstractConsumer;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\ServiceException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

abstract class AbstractProcessDlq extends AbstractConsumer implements ConfigAwareInterface
{
    use QueueAwareTrait;
    use ConfigAwareTrait;

    public const MAX_NUMBER_OF_MESSAGES = 10;

    /**
     * Subject line for the email.
     *
     * @var string
     */
    protected $emailSubject;

    /**
     * Name of the command handler of the original queue item
     *
     * @var string
     */
    protected $queueType;

    /**
     * @inheritdoc
     */
    protected $repoServiceName = 'MessageFailures';

    /**
     * @inheritdoc
     */
    protected $extraRepos = ['Organisation'];

    /**
     * @param CommandInterface $command
     *
     * @return Result
     * @throws RuntimeException
     * @throws ServiceException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        $organisationRepo = $this->getRepo('Organisation');
        $messageFailuresRepo = $this->getRepo('MessageFailures');
        $organisationNumbers = [];
        $emailAddress = $this->getConfig()['company_house_dlq']['notification_email_address'];

        while ($messages = $this->fetchMessages(self::MAX_NUMBER_OF_MESSAGES)) {
            foreach ($messages as $message) {
                $companyNumber = $message['Body'];
                $organisations = $organisationRepo->getByCompanyOrLlpNo($companyNumber);

                $messageFailure = new MessageFailures();
                $messageFailure->setOrganisation($organisations[0]);
                $messageFailure->setQueueType($this->queueType);
                $messageFailuresRepo->saveOnFlush($messageFailure);

                $this->deleteMessage($message);
                $organisationNumbers[] = $companyNumber;
            }
        }

        if (empty($organisationNumbers)) {
            $this->noMessages();
            return $this->result;
        }

        $messageFailuresRepo->flushAll();

        $params = [
            'organisationNumbers' => array_unique($organisationNumbers),
            'emailAddress' => $emailAddress,
            'emailSubject' => $this->emailSubject
        ];
        $this->result->merge($this->handleSideEffect(SendFailedOrganisationsList::create($params)));

        return $this->result;
    }
}
