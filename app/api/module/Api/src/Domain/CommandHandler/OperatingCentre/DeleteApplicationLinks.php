<?php

/**
 * Delete Operating Centre Application Links
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\OperatingCentre;

use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteApplicationLinks as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Operating Centre Application Links
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class DeleteApplicationLinks extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ApplicationOperatingCentre';

    /**
     * @param Cmd $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var OperatingCentreEntity $operatingCentre */
        $operatingCentre = $command->getOperatingCentre();

        $count = 0;
        if ($operatingCentre->getApplications()) {
            foreach ($operatingCentre->getApplications() as $aoc) {
                if ($aoc->getApplication()->isUnderConsideration()) {
                    $this->getRepo('ApplicationOperatingCentre')->delete($aoc);
                    $count++;
                }
            }
        }

        $this->result->addMessage('Delinked Operating Centre from ' . $count . ' other Application(s)');

        return $this->result;
    }
}
