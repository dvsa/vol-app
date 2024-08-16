<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\Update as Command;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;

/**
 * Update ContinuationDetail
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Update extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Command */

        /* @var $continuationDetail ContinuationDetail */
        $continuationDetail = $this->getRepo()->fetchById(
            $command->getId(),
            \Doctrine\ORM\Query::HYDRATE_OBJECT,
            $command->getVersion()
        );

        if ($command->getStatus()) {
            $continuationDetail->setStatus($this->getRepo()->getRefdataReference($command->getStatus()));
        }
        if ($command->getReceived()) {
            $continuationDetail->setReceived($command->getReceived());
        }
        if ($command->getTotAuthVehicles()) {
            $continuationDetail->setTotAuthVehicles($command->getTotAuthVehicles());
        }
        if ($command->getTotCommunityLicences()) {
            $continuationDetail->setTotCommunityLicences($command->getTotCommunityLicences());
        }
        if ($command->getTotPsvDiscs()) {
            $continuationDetail->setTotPsvDiscs($command->getTotPsvDiscs());
        }

        $this->getRepo()->save($continuationDetail);

        $result = new Result();
        $result->addId('continuationDetail', $continuationDetail->getId());
        $result->addMessage('ContinuationDetail updated');

        return $result;
    }
}
