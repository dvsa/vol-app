<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\BulkSend;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Email\AbstractEmailHandler;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Template\Template;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Handle email from bulk report upload
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ProcessEmail extends AbstractEmailHandler
{
    protected $repoServiceName = 'Licence';

    /**
     * Sends email to licence specifed on bulk upload sheet.
     *
     * @param CommandInterface $command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Dvsa\Olcs\Email\Exception\EmailNotSentException
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $this->template = $command->getTemplateName();
        $this->subject = $this->getSubjectLine();
        return parent::handleCommand($command);
    }

    /**
     * Returns a string to use as subject line for email
     *
     * @return string
     */
    private function getSubjectLine()
    {
        return
            array_key_exists($this->template, Template::BULK_TEMPLATE_SUBJECT_MAP)
                ? Template::BULK_TEMPLATE_SUBJECT_MAP[$this->template]
                : 'Important information about your vehicle operator licence';
    }

    /**
     * Get template variables
     *
     * @param Licence $recordObject
     *
     * @return array
     */
    #[\Override]
    protected function getTemplateVariables($recordObject): array
    {
        $vars = [
            'licenceType' => $recordObject->getLicenceType()->getDescription(),
            'goodsOrPsv' => $recordObject->getGoodsOrPsv()->getDescription()
        ];

        return $vars;
    }

    /**
     * @param object $recordObject
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\MissingEmailException
     */
    protected function getRecipients($recordObject): array
    {
        return $this->organisationRecipients($recordObject->getOrganisation());
    }

    /**
     * Generate task appropriate for the type of email being sent
     *
     * @param mixed $recordObject
     * @param Result $result
     * @param MissingEmailException $exception
     * @return Result
     */
    protected function createMissingEmailTask($recordObject, Result $result, MissingEmailException $exception): Result
    {
        $taskData = [
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => Category::TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK,
            'description' => 'Unable to send email - no organisation recipients found for Org: ' . $recordObject->getOrganisation()->getName() . ' - Please update the organisation admin user contacts to ensure at least one has a valid email address.',
            'actionDate' => (new DateTime())->format('Y-m-d'),
            'licence' => $recordObject->getId(),
            'urgent' => 'Y'
        ];

        $result->merge($this->handleSideEffect(\Dvsa\Olcs\Api\Domain\Command\Task\CreateTask::create($taskData)));
        $result->addMessage($exception->getMessage());
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getTranslateToWelsh($recordObject)
    {
        return $recordObject->getTranslateToWelsh();
    }
}
