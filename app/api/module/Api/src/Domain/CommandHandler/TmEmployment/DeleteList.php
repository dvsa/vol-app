<?php

/**
 * Delete a list of TM Employment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a list of TM Employment
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteList extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TmEmployment';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $tmeId) {
            /* @var $tme TmEmployment */
            $tme = $this->getRepo()->fetchById($tmeId);
            $this->getRepo()->delete($tme);
            $result->addMessage("TM Employment ID {$tmeId} deleted");
        }

        return $result;
    }
}
