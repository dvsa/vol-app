<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore as GenerateAndStoreCmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\CreateLetter as CreateLetterCommand;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Create Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateLetter extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    public const array DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FIRST = [
        'templates/PUB_APPS_SUPP_DOCS_1ST.rtf',
        'templates/GB/ENV_PUB_APPS_SUPP_DOCS_1ST.rtf',
        'templates/GB/PSV_NEW_APP_SUPP_DOCS_1ST.rtf',
        'templates/GB/PSV_VAR_APP_SUPP_DOCS_1ST.rtf',
        'templates/NI/PUB_APPS_SUPP_DOCS_1ST.rtf',
        'templates/GB/Incompletenon-digitalNewapp1strequestforsupportingdocs.rtf',
        'templates/GB/Incompletenon-digitalVarapp1strequestforsupportingdocs.rtf',
    ];

    public const array DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FINAL = [
        'templates/GB/PUB_APPS_SUPP_DOCS_FINAL.rtf',
        'templates/GB/ENV_PUB_APPS_SUPP_DOCS_FINAL.rtf',
        'templates/GB/PSV_NEW_APP_SUPP_DOCS_FINAL.rtf',
        'templates/GB/PSV_VAR_APP_SUPP_DOCS_FINAL.rtf',
        'templates/NI/PUB_APPS_SUPP_DOCS_FINAL.rtf',
    ];

    protected $repoServiceName = 'DocTemplate';

    /**
     * Handle command
     *
     * @param CreateLetterCommand $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $template = $this->getRepo()->fetchById($command->getTemplate());
        assert($template instanceof DocTemplate, 'Expected instance of DocTemplate');

        $this->result->merge($this->generateDocument($template, $command));

        $this->handleFollowUpTasks($template, $command);

        return $this->result;
    }

    /**
     * Generate document
     *
     * @param DocTemplate $template template
     * @param CreateLetterCommand $command  command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    protected function generateDocument(DocTemplate $template, CreateLetterCommand $command)
    {
        $queryData = $command->getData();

        $dtoData = [
            'template' => $template->getDocument()->getIdentifier(),
            'query' => $queryData,
            'description' => $template->getDescription(),
            'category' => $queryData['details']['category'],
            'subCategory' => $queryData['details']['documentSubCategory'],
            'isExternal' => false,
            'isScan' => false,
            'metadata' => $command->getMeta(),
            'disableBookmarks' => $command->getDisableBookmarks()
        ];

        try {
            return  $this->handleSideEffect(GenerateAndStoreCmd::create($dtoData));
        } catch (\Exception $e) {
            throw new ValidationException([$e->getMessage()]);
        }
    }

    /**
     * @throws \Exception
     */
    protected function handleFollowUpTasks(DocTemplate $template, CreateLetterCommand $command)
    {
        $this->handleFollowUpTaskForApplicationOrVariationFirstLetter($template, $command);
        $this->handleFollowUpTaskForApplicationFinalLetter($template, $command);
    }

    /**
     * Generates a task for an application or variation when a 1ST letter is created.
     *
     * @throws \Exception
     */
    protected function handleFollowUpTaskForApplicationOrVariationFirstLetter(DocTemplate $template, CreateLetterCommand $command)
    {
        if (
            ! in_array(
                $this->resolveTemplateIdentifier($template->getDocument()->getIdentifier()),
                static::DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FIRST
            )
        ) {
            return;
        }

        $applicationId = $command->getData()['application'] ?? null;
        if (is_null($applicationId)) {
            throw new \Exception('Expected `applicationId` when creating a task for first letter.');
        }

        $licenceId = $command->getData()['licence'] ?? null;
        if (is_null($licenceId)) {
            throw new \Exception('Expected `licenceId` when creating a task for first letter.');
        }

        $actionDate = new DateTime();
        $actionDate->add(new \DateInterval('P14D'));

        $currentUser = $this->getCurrentUser()->getId();

        $this->createTask([
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_RESPONSE_TO_FIRST_REQUEST,
            'description' => 'Check response to first letters',
            'application' => $applicationId,
            'licence' => $licenceId,
            'assignedToUser' => $currentUser,
            'assignedByUser' => $currentUser,
            'urgent' => 'N',
            'actionDate' => $actionDate->format('Y-m-d')
        ]);
    }

    /**
     * Generated a task for an application or variation when a FINAL letter is created.
     *
     * @throws \Exception
     */
    protected function handleFollowUpTaskForApplicationFinalLetter(DocTemplate $template, CreateLetterCommand $command)
    {
        if (
            ! in_array(
                $this->resolveTemplateIdentifier($template->getDocument()->getIdentifier()),
                static::DOCUMENT_TEMPLATE_IDENTIFIERS_FOLLOW_UP_FINAL
            )
        ) {
            return;
        }

        $applicationId = $command->getData()['application'] ?? null;
        if (is_null($applicationId)) {
            throw new \Exception('Expected `applicationId` when creating a task for final letter.');
        }

        $licenceId = $command->getData()['licence'] ?? null;
        if (is_null($licenceId)) {
            throw new \Exception('Expected `licenceId` when creating a task for final letter.');
        }

        $actionDate = new DateTime();
        $actionDate->add(new \DateInterval('P14D'));

        $currentUser = $this->getCurrentUser()->getId();

        $this->createTask([
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_RESPONSE_TO_FINAL_REQUEST,
            'description' => 'Check response to final letters',
            'application' => $applicationId,
            'licence' => $licenceId,
            'assignedToUser' => $currentUser,
            'assignedByUser' => $currentUser,
            'urgent' => 'N',
            'actionDate' => $actionDate->format('Y-m-d')
        ]);
    }

    /**
     * Creates a task.
     */
    protected function createTask(array $arrayDto)
    {
        $taskDto = CreateTask::create($arrayDto);
        $this->result->merge($this->handleSideEffect($taskDto));
    }

    /**
     * Resolves template identifier.
     *  - Ignores prefixed forward slash
     *
     * @return string
     */
    private function resolveTemplateIdentifier(string $template): string
    {
        return ltrim($template, "/");
    }
}
