<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Approve IrfoPsvAuth
 */
final class ApproveIrfoPsvAuth extends AbstractCommandHandler implements TransactionedInterface
{
    use IrfoPsvAuthUpdateTrait;

    protected $repoServiceName = 'IrfoPsvAuth';

    protected $extraRepos = ['Fee'];

    /**
     * Handle Approve command
     *
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        // common IRFO PSV Auth update
        $irfoPsvAuth = $this->updateIrfoPsvAuth($command);

        $irfoPsvAuth->approve(
            $this->getRepo()->getRefdataReference(IrfoPsvAuth::STATUS_APPROVED),
            $this->getRepo('Fee')->fetchFeesByIrfoPsvAuthId($irfoPsvAuth->getId(), true)
        );

        $this->getRepo()->save($irfoPsvAuth);

        $result = new Result();
        $result->addId('irfoPsvAuth', $irfoPsvAuth->getId());
        $result->addMessage('IRFO PSV Auth approved successfully');

        return $result;
    }
}
