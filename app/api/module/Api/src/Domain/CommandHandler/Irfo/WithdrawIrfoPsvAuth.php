<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelIrfoPsvAuthFees as CancelFeesDto;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Withdraw IrfoPsvAuth
 */
final class WithdrawIrfoPsvAuth extends AbstractCommandHandler implements TransactionedInterface
{
    use IrfoPsvAuthUpdateTrait;

    protected $repoServiceName = 'IrfoPsvAuth';

    /**
     * Handle Withdraw command
     *
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        // common IRFO PSV Auth update
        $irfoPsvAuth = $this->updateIrfoPsvAuth($command);

        $irfoPsvAuth->withdraw(
            $this->getRepo()->getRefdataReference(IrfoPsvAuth::STATUS_WITHDRAWN)
        );

        $this->getRepo()->save($irfoPsvAuth);

        $result = new Result();
        $result->addId('irfoPsvAuth', $irfoPsvAuth->getId());

        // cancel all associated fees
        $result->merge($this->cancelFees($command));

        $result->addId('irfoPsvAuth', $irfoPsvAuth->getId());
        $result->addMessage('IRFO PSV Auth withdrawn successfully');

        return $result;
    }

    /**
     * Cancel all associated fees, but IRFOPSVAPP
     *
     * @return Result
     */
    private function cancelFees(CommandInterface $command)
    {
        return $this->handleSideEffect(
            CancelFeesDto::create(
                [
                    'id' => $command->getId(),
                    'exclusions' => []
                ]
            )
        );
    }
}
