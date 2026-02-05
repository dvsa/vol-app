<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use DateTimeImmutable;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Domain\Repository\Bus;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\System\Category;

final class SendBSRNotificationToLTAs extends AbstractEmailHandler implements EmailAwareInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = Bus::class;
    protected $template = 'bsr-lta-email-notification';
    protected $subject = 'email.bsr-lta-email-notification.subject';


    /**
     * @param $recordObject
     * @return array
     * @throws MissingEmailException
     */
    protected function getRecipients($recordObject): array
    {
        $recipients = [];
        $invalidEmails = [];

        $emailValidator = new \Laminas\Validator\EmailAddress();

        foreach ($recordObject->getLocalAuthoritys() as $lta) {
            $email = $lta->getEmailAddress();

            if ($emailValidator->isValid($email)) {
                $recipients[] = $email;
            } else {
                $invalidEmails[] = $lta;
            }
        }

        if (!empty($invalidEmails)) {
            $this->createInvalidRecipientTask($recordObject);
        }

        if (empty($recipients)) {
            throw new MissingEmailException('No associated LTAs have valid email addresses!');
        }

        return [
            'to' => array_shift($recipients),  // Send to the first valid email
            'cc' => $recipients,                      // CC the rest
            'bcc' => [],
        ];
    }


    /**
     * @param $recordObject
     * @param Result $result
     * @param MissingEmailException $exception
     * @return Result
     */
    protected function createMissingEmailTask($recordObject, Result $result, MissingEmailException $exception): Result
    {
        $taskDescription = sprintf(
            'Unable to send BSR Notification email for Reg No: %s - No associated LTAs have email addresses' .
            ' - Please update the Local Authority records to ensure all have email addresses.',
            $recordObject->getRegNo()
        );

        $taskData = $this->buildTaskData($taskDescription, $recordObject);
        $result->merge($this->handleSideEffect(CreateTask::create($taskData)));
        $result->addMessage($exception->getMessage());
        return $result;
    }


    /**
     * @param $recordObject
     * @return void
     */
    protected function createInvalidRecipientTask($recordObject)
    {
        $taskDescription = sprintf(
            'At least one LTA named on the Bus Reg No: %s has an invalid email address.' .
            ' Please update the Local Authority Records to ensure all have email addresses.',
            $recordObject->getRegNo()
        );

        $taskData = $this->buildTaskData($taskDescription, $recordObject);
        $this->handleSideEffect(CreateTask::create($taskData));
    }

    /**
     * Helper method to build task data for task creation.
     *
     * @param string $description The task description.
     * @param BusReg $recordObject The bugreg record.
     * @return array The task data array.
     */
    protected function buildTaskData(string $description, $recordObject): array
    {
        $actionDate = (new DateTimeImmutable())->format('Y-m-d');
        $currentUser = $this->getCurrentUser();
        return [
            'category'    => Category::CATEGORY_BUS_REGISTRATION,
            'subCategory' => Category::BUS_SUB_CATEGORY_OTHER_DOCUMENTS,
            'description' => $description,
            'actionDate'  => $actionDate,
            'busReg'      => $recordObject->getId(),
            'licence'     => $recordObject->getLicence()->getId(),
            'urgent'      => 'Y',
            'assignedToUser' => $currentUser->getId(),
            'assignedToTeam' => $currentUser->getTeam()->getId(),
        ];
    }

    #[\Override]
    protected function getTranslateToWelsh($recordObject)
    {
        return 'N';
    }
}
