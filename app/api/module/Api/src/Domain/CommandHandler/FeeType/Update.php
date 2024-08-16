<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\FeeType;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Transfer\Command\FeeType\Update as UpdateFeeTypeCmd;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;

/**
 * Update a Fee Type
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'FeeType';

    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var UpdateFeeTypeCmd $command
         * @var FeeTypeEntity $feeType
         * @var FeeTypeRepo $repo
         */
        $repo = $this->getRepo();
        $feeType = $repo->fetchUsingId($command);

        $newFeeType = $feeType->updateNewFeeType(
            $command->getEffectiveFrom(),
            $command->getFixedValue(),
            $command->getAnnualValue(),
            $command->getFiveYearValue(),
            $feeType
        );

        $repo->save($newFeeType);

        $this->result->addId('FeeType', $newFeeType->getId());
        $this->result->addMessage("Fee Type updated");
        return $this->result;
    }
}
