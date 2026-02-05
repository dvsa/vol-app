<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Bus\CreateNoticePeriod as CreateNoticePeriodCmd;
use Dvsa\Olcs\Api\Domain\Repository\BusNoticePeriod as BusNoticePeriodRepo;

final class CreateNoticePeriod extends AbstractCommandHandler
{
    public const string SUCCESS_MSG = 'Bus Notice Period created';

    protected $repoServiceName = 'BusNoticePeriod';

    /**
     * @param CommandInterface|CreateNoticePeriodCmd $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $noticePeriod = BusNoticePeriod::createNew(
            $command->getNoticeArea(),
            $command->getStandardPeriod()
        );

        $repo = $this->getRepo();
        assert($repo instanceof BusNoticePeriodRepo);
        $repo->save($noticePeriod);

        $this->result->addId($this->repoServiceName, $noticePeriod->getId());
        $this->result->addMessage(self::SUCCESS_MSG);

        return $this->result;
    }
}
