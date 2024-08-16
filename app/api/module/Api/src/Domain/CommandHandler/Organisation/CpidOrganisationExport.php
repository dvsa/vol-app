<?php

/**
 * CpidOrganisationExport.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Organisation;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;

/**
 * Class CpidOrganisationExport
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CpidOrganisationExport extends AbstractCommandHandler
{
    protected $repoServiceName = 'Queue';

    public function handleCommand(CommandInterface $command)
    {
        $dtoData = [
            'options' => json_encode(['status' => $command->getCpid()]),
            'type' => Queue::TYPE_CPID_EXPORT_CSV,
            'status' => Queue::STATUS_QUEUED
        ];
        $this->result->merge($this->handleSideEffect(Create::create($dtoData)));
        return $this->result;
    }
}
