<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Transfer\Command\Auth\RefreshTokens as RefreshTokensCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

class RefreshTokens extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public function __construct(protected ValidatableAdapterInterface $adapter)
    {
    }

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof RefreshTokensCommand);

        $result = $this->adapter->refreshToken($command->getRefreshToken(), $command->getUsername());

        $this->result->setFlag('isValid', $result->isValid());
        $this->result->setFlag('code', $result->getCode());
        $this->result->setFlag('identity', $result->getIdentity());
        $this->result->setFlag('messages', $result->getMessages());

        return $this->result;
    }
}
