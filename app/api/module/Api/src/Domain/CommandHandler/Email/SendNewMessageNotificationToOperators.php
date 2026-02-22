<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use DateTimeImmutable;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category;

final class SendNewMessageNotificationToOperators extends AbstractEmailHandler implements EmailAwareInterface
{
    protected $repoServiceName = LicenceRepo::class;
    protected $template = 'messaging-new-message-operator';
    protected $subject = 'email.new-message-for-operator.subject';

    /** @param LicenceEntity $recordObject */
    protected function getRecipients($recordObject): array
    {
        return $this->organisationRecipients($recordObject->getOrganisation());
    }

    /** @param LicenceEntity $recordObject */
    #[\Override]
    protected function getTranslateToWelsh($recordObject)
    {
        return $recordObject->getTranslateToWelsh();
    }

    /** @param LicenceEntity $recordObject */
    protected function createMissingEmailTask($recordObject, Result $result, MissingEmailException $exception): Result
    {
        $taskData = [
            'category'        => Category::CATEGORY_LICENSING,
            'subCategory'     => Category::TASK_SUB_CATEGORY_LICENSING_GENERAL_TASK,
            'description'     => sprintf(
                'Unable to send email - no organisation recipients found for Org: %s' .
                ' - Please update the organisation admin user contacts to ensure at least one has a valid email address.',
                $recordObject->getOrganisation()->getName(),
            ),
            'actionDate'      => (new DateTimeImmutable())->format('Y-m-d'),
            'licence'         => $recordObject->getId(),
            'urgent'          => 'Y',
        ];

        $result->merge($this->handleSideEffect(CreateTask::create($taskData)));
        $result->addMessage($exception->getMessage());
        return $result;
    }
}
