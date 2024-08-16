<?php

/**
 * Assign Submission
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Transfer\Command\Submission\AssignSubmission as Cmd;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\SubmissionGeneratorAwareTrait;
use Dvsa\Olcs\Api\Domain\SubmissionGeneratorAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;

/**
 * Assign Submission
 */
final class AssignSubmission extends AbstractCommandHandler implements
    AuthAwareInterface,
    SubmissionGeneratorAwareInterface,
    TransactionedInterface
{
    use SubmissionGeneratorAwareTrait;
    use AuthAwareTrait;


    protected $repoServiceName = 'Submission';

    protected $assignedToUserId = null;

    protected $extraRepos = ['User', 'Task'];

    /**
     * @param CommandInterface $command
     *
     * @return Result
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {

        $submissionEntity = $this->updateSubmission($command);
        $this->getRepo()->save($submissionEntity);
        $result = new Result();
        $result->addId('submission', $submissionEntity->getId());
        $result->addMessage('Submission updated successfully');

        $result->merge($this->handleSideEffect($this->createTaskCommand($command)));

        return $result;
    }

    /**
     *
     * @return Submission
     * @throws ValidationException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function updateSubmission(Cmd $command)
    {
        /** @var Submission $submission */
        $submission = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        if (empty($submission->getInformationCompleteDate())) {
            throw new ValidationException(
                [
                    'NOT_COMPLETE' => 'Cannot assign submission until information complete date is set'
                ]
            );
        }

        // Only allow assigned date to be changed if the SLA hasnt started already.
        if (!$submission->getTcSlaStarted()) {
            $submission->setAssignedDate(new DateTime());
        }

        switch ($command->getTcOrOther()) {
            case 'tc':
                $this->assignedToUserId = $command->getPresidingTcUser();
                $submission->setTcSlaStarted(1);
                break;
            case 'other':
                $this->assignedToUserId = $command->getRecipientUser();
                break;
        }

        $submission->setRecipientUser(
            $this->getRepo()->getReference(UserEntity::class, $this->assignedToUserId)
        );

        $currentUser = $this->getCurrentUser();

        $submission->setSenderUser($currentUser);

        if ($command->getUrgent() !== null) {
            $submission->setUrgent($command->getUrgent());
        }

        return $submission;
    }

    /**
     * This method fetches an existing task for the submission, if it exists, close it and create a new one.
     *
     *
     * @return static
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createTaskCommand(Cmd $command)
    {
        $submission = $this->getRepo()->fetchWithCaseAndLicenceById($command->getId());

        /** @var TaskEntity $task */
        $task = $this->getRepo('Task')->fetchAssignedToSubmission($submission);

        /** @var UserEntity $recipientUser */
        $recipientUser = $this->getRepo('User')->fetchById($this->assignedToUserId);

        $teamId = null;
        if ($recipientUser->getTeam() instanceof TeamEntity) {
            $teamId = $recipientUser->getTeam()->getId();
        }

        // if task exists, mark complete, and create another
        if (!empty($task)) {
            $task->setIsClosed('Y');
            $this->getRepo('Task')->save($task);
            $this->result->addMessage($task->getId() . ' Task closed');
        }

        // create new task

        $data = [
            'category' => TaskEntity::CATEGORY_SUBMISSION,
            'subCategory' => TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT,
            'actionDate' => date('Y-m-d'),
            'assignedToUser' => $recipientUser->getId(),
            'assignedToTeam' => $teamId,
            'assignedByUser' => $this->getCurrentUser()->getId(),
            'case' => $submission->getCase()->getId(),
            'submission' => $submission->getId(),
            'urgent' => $command->getUrgent(),
            'isClosed' => 0
        ];

        // generate description and licence OR TM data
        if ($submission->getCase()->isTm()) {
            $tmId = $submission->getCase()->getTransportManager()->getId();
            $data['description'] = 'Transport Manager ' . $tmId . ' Case ' . $submission->getCase()->getId() .
                ' Submission ' . $submission->getId();
            $data['transportManager'] = $tmId;
        } else {
            $licenceId = $submission->getCase()->getLicence()->getId();
            $licenceNo = $submission->getCase()->getLicence()->getLicNo();

            $data['description'] = 'Licence ' . $licenceNo . ' Case ' . $submission->getCase()->getId() .
                ' Submission ' . $submission->getId();
            $data['licence'] = $licenceId;
        }

        return CreateTaskCmd::create($data);
    }
}
