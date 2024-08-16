<?php

/**
 * Create SubmissionAction
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Schedule41;

use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Schedule41 as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Class CreateS4
 *
 * Create an S4 record.
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Schedule41
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class CreateS4 extends AbstractCommandHandler
{
    protected $repoServiceName = 'S4';

    public function handleCommand(CommandInterface $command)
    {
        $s4 = new S4($command->getApplication(), $command->getLicence());

        $s4->setReceivedDate($command->getReceivedDate());
        $s4->setSurrenderLicence($command->getSurrenderLicence());

        $this->getRepo()->save($s4);

        $result = new Result();
        $result->addId('s4', $s4->getId());
        $result->addMessage('S4 record created.');

        return $result;
    }
}
