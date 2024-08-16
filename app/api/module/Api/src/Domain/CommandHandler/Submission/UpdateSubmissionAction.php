<?php

/**
 * Update SubmissionAction
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Pi\Reason;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionAction;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Update SubmissionAction
 */
final class UpdateSubmissionAction extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'SubmissionAction';

    public function handleCommand(CommandInterface $command)
    {
        $submissionAction = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // Backend validate
        if (
            !empty(
                array_intersect(
                    $command->getActionTypes(),
                    [
                    SubmissionAction::ACTION_TYPE_PUBLIC_INQUIRY,
                    SubmissionAction::ACTION_TYPE_TM_PUBLIC_INQUIRY,
                    SubmissionAction::ACTION_TYPE_PROPOSE_TO_REVOKE
                    ]
                )
            )
            && empty($command->getReasons())
            && ($submissionAction->getIsDecision() === 'N')
        ) {
            throw new ValidationException(
                [
                    'actionTypes' => [
                        SubmissionAction::ERROR_ACTION_REQUIRES_LEGISLATION
                    ]
                ]
            );
        }

        $actionTypes = array_map(
            fn($actionTypeId) => $this->getRepo()->getRefdataReference($actionTypeId),
            $command->getActionTypes()
        );

        $submissionAction->update($actionTypes, $command->getComment());

        if ($command->getReasons() !== null) {
            $reasons = array_map(
                fn($reasonId) => $this->getRepo()->getReference(Reason::class, $reasonId),
                $command->getReasons()
            );
            $submissionAction->setReasons($reasons);
        }

        $this->getRepo()->save($submissionAction);

        $result = new Result();
        $result->addId('submissionAction', $submissionAction->getId());
        $result->addMessage('Submission Action updated successfully');

        return $result;
    }
}
