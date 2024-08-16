<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use DateTimeImmutable;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Domain\Repository\Bus;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\System\Category;

final class SendBSRNotificationToLTAs extends AbstractEmailHandler implements EmailAwareInterface
{
    protected $repoServiceName = Bus::class;
    protected $template = 'bsr-lta-email-notification';
    protected $subject = 'email.bsr-lta-email-notification.subject';

    /** @param BusReg $recordObject */
    protected function getRecipients($recordObject): array
    {
        foreach ($recordObject->getLocalAuthoritys() as $lta) {
            $recipients[] = $lta->getEmailAddress();
        }

        if (empty($recipients)) {
            throw new MissingEmailException('No associated LTAs have an email addresses!');
        }

        return [
            'to' => array_shift($recipients),
            'cc' => $recipients,
            'bcc' => [],
        ];
    }

    protected function getTranslateToWelsh($recordObject)
    {
        return 'N';
    }

    /** @param BusReg $recordObject */
    protected function createMissingEmailTask($recordObject, Result $result, MissingEmailException $exception): Result
    {
        $taskData = [
            'category'        => Category::CATEGORY_BUS_REGISTRATION,
            'subCategory'     => Category::BUS_SUB_CATEGORY_OTHER_DOCUMENTS,
            'description'     => sprintf(
                'Unable to send BSR Notification email for Reg No: %s - No associated LTAs have an email addresses!' .
                '- Please update the Local Authority records to ensure all have email addresses.',
                $recordObject->getRegNo(),
            ),
            'actionDate'      => (new DateTimeImmutable())->format('Y-m-d'),
            'busReg'          => $recordObject->getId(),
            'urgent'          => 'Y',
        ];

        $result->merge($this->handleSideEffect(CreateTask::create($taskData)));
        $result->addMessage($exception->getMessage());
        return $result;
    }
}
