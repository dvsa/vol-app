<?php

/**
 * Replace IRHP Permit
 *
 * @author Andy Newton <andy@vitri.ltd>
 *
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\ReplacementIrhpPermit;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Query\IrhpPermit\ByPermitNumber;
use Dvsa\Olcs\Api\Domain\Query\IrhpPermitRange\ByPermitNumber as RangeByPermitNumber;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class Replace extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpPermit';

    /**
     * Handle Replace Permit command
     *
     * @param CommandInterface $command Command
     *
     * @return Result
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        // Retrieve old permit and verify that it is in a valid state to replace
        /** @var IrhpPermit $oldPermit */
        $oldPermit = $this->getRepo()->fetchById($command->getId());
        if (!$oldPermit->isPrinted()) {
            throw new ValidationException(['Only "Printed" Permits can be replaced']);
        }

        $targetRange = $this->findTargetRange(
            $command,
            $oldPermit->getIrhpPermitRange()->getIrhpPermitStock()->getId()
        );

        if ($this->checkNewPermitFree($command, $targetRange)) {
            $cmdData = [
                'replaces' => $oldPermit->getId(),
                'irhpPermitRange' => $targetRange->getId(),
                'permitNumber' => $command->getReplacementIrhpPermit()
            ];
            $this->result->merge(
                $this->handleSideEffect(
                    ReplacementIrhpPermit::create($cmdData)
                )
            );
        }

        $ceaseStatus = $this->refData(IrhpPermit::STATUS_CEASED);
        $oldPermit->cease($ceaseStatus);
        $this->getRepo()->save($oldPermit);

        $this->result->addId('IrhpPermit', $oldPermit->getId());
        $this->result->addMessage('The replacement permit has been successfully issued and can now be printed');

        return $this->result;
    }


    // Use the user-provided permit number, and the stock ID of the permit being replaced to find the target range

    /**
     * @param CommandInterface $command Command
     * @param int $oldPermitStockId
     * @return mixed
     * @throws NotFoundException
     */
    protected function findTargetRange($command, $oldPermitStockId)
    {
        $targetRange = $this->handleQuery(
            RangeByPermitNumber::create(
                [
                    'permitNumber' => $command->getReplacementIrhpPermit(),
                    'permitStock' => $oldPermitStockId
                ]
            )
        );

        if (count($targetRange) != 1) {
            throw new NotFoundException('You must input an available Permit Number from a replacement number range from the same stock as the permit being replaced');
        }

        return $targetRange[0];
    }

    // Verify that the provided permit number has not already been used in the replacement range

    /**
     * @param CommandInterface $command Command
     * @param IrhpPermitRange $targetRange
     * @return bool
     * @throws BadRequestException
     */
    protected function checkNewPermitFree($command, $targetRange)
    {
        $newPermit = $this->handleQuery(
            ByPermitNumber::create(
                [
                    'permitNumber' => $command->getReplacementIrhpPermit(),
                    'irhpPermitRange' => $targetRange->getId()
                ]
            )
        );

        if (!empty($newPermit)) {
            throw new BadRequestException('The inputted Permit Number is not available. Please input a different number from the same range');
        }

        return true;
    }
}
