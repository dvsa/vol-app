<?php

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
    public function handleCommand(CommandInterface $command)
    {
        return new Result();
    }

    public function getExtraRepos()
    {
        return $this->extraRepos;
    }

    public function setExtraRepos($extraRepos)
    {
        $this->extraRepos = $extraRepos;
    }

    public function maybeDeleteContactDetailsAndAddressStub($contactDetails)
    {
        $this->maybeDeleteContactDetailsAndAddress($contactDetails);
    }

    public function injectReposStub()
    {
        $this->injectRepos();
    }
}
