<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Auth;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Auth\Adapter\CognitoAdapter;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;

class ChangePassword extends AbstractCommandHandler implements AuthAwareInterface
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
        assert($command instanceof \Dvsa\Olcs\Transfer\Command\Auth\ChangePassword);

        $changeResult = $this->adapter->changePassword(
            $this->getCurrentUser()->getLoginId(),
            $command->getPassword(),
            $command->getNewPassword()
        );

        $this->result->setFlag('code', $changeResult->getCode());
        $this->result->setFlag('message', $changeResult->getMessage());

        return $this->result;
    }
}
