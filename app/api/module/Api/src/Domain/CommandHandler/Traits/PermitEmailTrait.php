<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Entity\System\Category;

/**
 * Permit email trait
 */
trait PermitEmailTrait
{
    /**
     * Returns array of addresses based on the permit application
     * NOTE makes use of EmailAwareTrait, try to think of a better way of arranging this
     *
     * @param object $recordObject
     *
     * @return array
     */
    protected function getRecipients($recordObject): array
    {
        return $this->organisationRecipients(
            $recordObject->getLicence()->getOrganisation(),
            $recordObject->getCreatedBy()
        );
    }

    /**
     * Get subject variables
     *
     *
     * @return array
     */
    protected function getSubjectVariables(mixed $recordObject): array
    {
        return [
            'applicationRef' => $recordObject->getApplicationRef(),
        ];
    }

    /**
     * Generate task appropriate for the type of email being sent
     *
     * @return Result
     */
    protected function createMissingEmailTask(mixed $recordObject, Result $result, MissingEmailException $exception): Result
    {
        $taskData = [
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => Category::TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK,
            'description' => 'Unable to send email - no organisation recipients found for Org: ' . $recordObject->getLicence()->getOrganisation()->getName() . ' - Please update the organisation admin user contacts to ensure at least one has a valid email address.',
            'actionDate' => (new DateTime())->format('Y-m-d'),
            'licence' => $recordObject->getLicence()->getId(),
            'irhpApplication' => $recordObject->getId(),
            'urgent' => 'Y'
        ];

        $result->merge($this->handleSideEffect(\Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::create($taskData)));
        $result->addMessage($exception->getMessage());
        return $result;
    }

    /**
     * Calculate due date for payment
     *
     * @param string $date
     * @param int $daysToPayIssueFee
     *
     * @return string
     */
    protected function calculateDueDate(\DateTime $date, $daysToPayIssueFee)
    {
        $date->add(\DateInterval::createFromDateString('+' . ($daysToPayIssueFee - 1) . ' weekdays'));
        return $date->format('d F Y');
    }
}
