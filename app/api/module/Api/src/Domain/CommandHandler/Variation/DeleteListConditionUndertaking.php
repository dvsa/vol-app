<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * Delete a list of ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteListConditionUndertaking extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';

    /**
     * Command handler
     *
     * @param \Dvsa\Olcs\Transfer\Command\Variation\DeleteListConditionUndertaking $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Repository\ConditionUndertaking $repo */
        $repo = $this->getRepo();

        foreach ($command->getIds() as $cuId) {
            /* @var \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking $conditionUndertaking */
            $conditionUndertaking = $repo->fetchById($cuId);

            if ($conditionUndertaking->getApplication()) {
                $repo->delete($conditionUndertaking);
            } else {
                // create delta
                $deltaConditionUndertaking = clone $conditionUndertaking;
                $deltaConditionUndertaking
                    ->setLastModifiedOn(null)
                    ->setAction(ConditionUndertaking::ACTION_DELETE)
                    ->setLicConditionVariation($conditionUndertaking)
                    ->setLicence(null)
                    ->setApplication(
                        $repo->getReference(Application::class, $command->getId())
                    )
                    ->setOlbsKey(null)
                    ->setOlbsType(null);

                $repo->save($deltaConditionUndertaking);
            }

            $this->result->addMessage("ConditionUndertaking ID {$cuId} deleted");
        }

        $this->result->merge(
            $this->handleSideEffect(
                UpdateApplicationCompletionCmd::create(
                    [
                        'id' => $command->getId(),
                        'section' => 'conditionsUndertakings',
                    ]
                )
            )
        );

        return $this->result;
    }
}
