<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Nr;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractCommandConsumer;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Cases\Si\SendResponse as Cmd;
use Laminas\Serializer\Adapter\Json as LaminasJson;

/**
 * Request Map
 */
class SendMsiResponse extends AbstractCommandConsumer
{
    /**
     * @var string the command class
     */
    protected $commandName = Cmd::class;

    /**
     * gets command data
     *
     * @param QueueEntity $item
     * @return array
     */
    public function getCommandData(QueueEntity $item)
    {
        $json = new LaminasJson();
        return $json->unserialize($item->getOptions());
    }
}
