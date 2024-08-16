<?php

/**
 * Update IrfoPsvAuth
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update IrfoPsvAuth
 */
final class UpdateIrfoPsvAuth extends AbstractCommandHandler implements TransactionedInterface
{
    use IrfoPsvAuthFeeTrait;
    use IrfoPsvAuthUpdateTrait;

    protected $repoServiceName = 'IrfoPsvAuth';

    /**
     * Handle command
     *
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        // common IRFO PSV Auth update
        $irfoPsvAuth = $this->updateIrfoPsvAuth($command);

        $result = new Result();

        if ($irfoPsvAuth->getStatus()->getId() === IrfoPsvAuth::STATUS_RENEW) {
            // set the renewal date
            $irfoPsvAuth->setRenewalDate(new \DateTime());

            // change status to pending
            $irfoPsvAuth->setStatus(
                $this->getRepo()->getRefdataReference(IrfoPsvAuth::STATUS_PENDING)
            );

            // generate application fee
            $result->merge($this->generateApplicationFee($irfoPsvAuth));
        }

        $this->getRepo()->save($irfoPsvAuth);

        $result->addId('irfoPsvAuth', $irfoPsvAuth->getId());
        $result->addMessage('IRFO PSV Auth updated successfully');

        return $result;
    }
}
