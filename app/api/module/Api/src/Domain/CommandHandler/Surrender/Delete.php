<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Surrender;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Surrender\Delete as DeleteCmd;

final class Delete extends AbstractSurrenderCommandHandler
{
    protected $repoServiceName = "Surrender";

    /**
     * @param DeleteCmd $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $id = $command->getId();

        try {
            $this->getRepo()->delete($this->getRepo()->fetchOneByLicenceId($id));

            $this->result->addId('id' . $id, $id);
            $this->result->addMessage(sprintf('surrender for licence Id %d deleted', $id));
        } catch (NotFoundException) {
            $this->result->addMessage(sprintf('surrender for licence Id %d not found', $id));
        }

        return $this->result;
    }
}
