<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\CompaniesHouse;

use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;

/**
 * Abstract Companies House Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractConsumer extends AbstractCommandConsumer
{
    /**
     * Prepare command data based on Queue entity data
     *
     * @param QueueEntity $item Queue Entity
     *
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        $options = (array) json_decode($item->getOptions());

        return [
            'companyNumber' => $options['companyNumber']
        ];
    }
}
