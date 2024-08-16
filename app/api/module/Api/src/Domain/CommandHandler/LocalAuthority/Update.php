<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LocalAuthority;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LocalAuthority as LocalAuthorityRepo;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LocalAuthority\Update as UpdateLocalAuthorityCmd;

/**
 * Update a Local Authority
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LocalAuthority';

    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var UpdateLocalAuthorityCmd $command
         * @var LocalAuthorityEntity $localAuthority
         * @var LocalAuthorityRepo $repo
         */
        $repo = $this->getRepo();
        $localAuthority = $repo->fetchUsingId($command);
        $localAuthority->update(
            $command->getDescription(),
            $command->getEmailAddress()
        );

        $repo->save($localAuthority);

        $this->result->addId('LocalAuthority', $localAuthority->getId());
        $this->result->addMessage("Local Authority '{$localAuthority->getId()}' updated");
        return $this->result;
    }
}
