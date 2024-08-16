<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Restore a list of ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class RestoreListConditionUndertaking extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $cuId) {
            /* @var $conditionUndertaking \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking */
            $conditionUndertaking = $this->getRepo()->fetchById($cuId);

            if ($conditionUndertaking->getAction() === 'D') {
                $result->addMessage("ConditionUndertaking ID {$conditionUndertaking->getId()} restored");
                $this->getRepo()->delete($conditionUndertaking);
                continue;
            }

            if ($conditionUndertaking->getAction() === null) {
                $deltaConditionUndertakings = $this->getRepo()
                    ->fetchListForLicConditionVariation($conditionUndertaking->getId());

                // There should only be one, but...
                foreach ($deltaConditionUndertakings as $deltaConditionUndertaking) {
                    $this->getRepo()->delete($deltaConditionUndertaking);
                    $result->addMessage("ConditionUndertaking ID {$deltaConditionUndertaking->getId()} restored");
                }
            }
        }

        return $result;
    }
}
