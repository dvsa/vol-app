<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\CommunityLicence;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\ReportingBulkReprint as Cmd;

/**
 * Bulk reprint with reporting
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ReportingBulkReprint extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * Get command data
     *
     * @param QueueEntity $item item
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        $options = json_decode($item->getOptions(), true);
        return [
            'documentIdentifier' => $options['identifier'],
            'user' => $options['user']
        ];
    }
}
