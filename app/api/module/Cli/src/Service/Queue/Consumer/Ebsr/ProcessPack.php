<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Ebsr;

use Laminas\Serializer\Adapter\Json as LaminasJson;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPack as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Process EBSR Pack
 */
class ProcessPack extends AbstractCommandConsumer
{
    protected $commandName = Cmd::class;

    /**
     * @param QueueEntity $item
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        $json = new LaminasJson();
        return $json->unserialize($item->getOptions());
    }
}
