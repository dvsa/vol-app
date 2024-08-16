<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\GovUkAccount;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Service\GovUkAccount\GovUkAccountService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * GetGovUkAccountnRedirect
 */
class GetGovUkAccountRedirect extends AbstractCommandHandler
{
    /**
     * Constructor.
     */
    public function __construct(private readonly GovUkAccountService $govUkAccountService)
    {
    }

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\GovUkAccount\GetGovUkAccountRedirect $command Command to handle
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        $stateTokenStr = $this->govUkAccountService->createStateToken($command->getArrayCopy());
        $authorisationUrl = $this->govUkAccountService->getAuthorisationUrl($stateTokenStr, true);
        return $this->result->addMessage($authorisationUrl->getUrl());
    }
}
