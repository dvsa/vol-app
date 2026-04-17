<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\DeleteContactDetailsAndAddressTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class DeleteContactDetailsAndAddressTraitStub extends AbstractCommandHandler
{
    use DeleteContactDetailsAndAddressTrait;

    /**
     * @param CommandInterface $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        return new Result();
    }

    public function getExtraRepos(): mixed
    {
        return $this->extraRepos;
    }

    public function setExtraRepos(mixed $extraRepos): void
    {
        $this->extraRepos = $extraRepos;
    }

    public function maybeDeleteContactDetailsAndAddressStub(mixed $contactDetails): void
    {
        $this->maybeDeleteContactDetailsAndAddress($contactDetails);
    }

    public function injectReposStub(): void
    {
        $this->injectRepos();
    }
}
