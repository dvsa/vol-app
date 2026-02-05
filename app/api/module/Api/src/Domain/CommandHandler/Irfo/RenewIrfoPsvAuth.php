<?php

/**
 * Renew IrfoPsvAuth
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Renew IrfoPsvAuth
 */
final class RenewIrfoPsvAuth extends AbstractCommandHandler implements TransactionedInterface
{
    public const int MAX_IDS_COUNT = 100;

    protected $repoServiceName = 'IrfoPsvAuth';

    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getIds();

        if (count($ids) > self::MAX_IDS_COUNT) {
            throw new Exception\ValidationException(
                ['Number of selected records must be less than or equal to ' . self::MAX_IDS_COUNT]
            );
        }

        $status = $this->getRepo()->getRefdataReference(IrfoPsvAuth::STATUS_RENEW);

        $irfoPsvAuthList = $this->getRepo()->fetchByIds($ids);

        foreach ($irfoPsvAuthList as $irfoPsvAuth) {
            $irfoPsvAuth->renew($status);

            $this->getRepo()->save($irfoPsvAuth);
        }

        $result = new Result();
        $result->addMessage('IRFO PSV Auth renewed successfully');

        return $result;
    }
}
