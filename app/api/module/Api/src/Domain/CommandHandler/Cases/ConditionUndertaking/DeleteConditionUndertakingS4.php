<?php

/**
 * DeleteConditionUndertaking.php
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\S4;

/**
 * DeleteConditionUndertaking
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
final class DeleteConditionUndertakingS4 extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';

    public function handleCommand(CommandInterface $command)
    {
        $s4 = $this->getRepo()->getReference(S4::class, $command->getS4());
        $conditionsUndertakings = $this->getRepo()->fetchListForS4($s4->getId());

        foreach ($conditionsUndertakings as $conditionUndertaking) {
            $this->getRepo()->delete($conditionUndertaking);
        }

        $result = new Result();
        $result->addMessage('Conditions & Undertakings removed.');

        return $result;
    }
}
