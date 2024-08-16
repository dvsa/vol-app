<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\CommunityLicence;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Licence\Create as Cmd;

/**
 * Create community licence for licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateForLicence extends AbstractCommandConsumer
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
            'licence' => $options['licence'],
            'totalLicences' => $options['totalLicences']
        ];
    }
}
