<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class UpdateFeeStatus extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Fee';

    /**
     * @param CommandInterface $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $status = $command->getStatus();

        /** @var Fee $fee */
        $fee = $this->getRepo()->fetchById($command->getId());
        $fee->setFeeStatus($this->getRepo()->getRefdataReference($status));
        $this->getRepo()->save($fee);

        $this->result
            ->addId('fee', $fee->getId())
            ->addMessage('Fee status updated:' . $status);

        return $this->result;
    }
}
