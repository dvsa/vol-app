<?php

/**
 * Delete Change Of Entity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ChangeOfEntity;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Change Of Entity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class DeleteChangeOfEntity extends AbstractCommandHandler
{
    protected $repoServiceName = 'ChangeOfEntity';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $changeOfEntity ChangeOfEntityEntity */
        $changeOfEntity = $this->getRepo()->fetchUsingId($command);

        $this->getRepo()->delete($changeOfEntity);

        $result = new Result();
        $result->addId("changeOfEntity", $changeOfEntity->getId());
        $result->addMessage("ChangeOfEntity Deleted");

        return $result;
    }
}
