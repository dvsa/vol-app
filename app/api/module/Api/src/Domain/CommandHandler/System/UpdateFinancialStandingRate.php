<?php

/**
 * Update Financial Standing Rate
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\System;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\FinancialStandingRateRulesTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\System\UpdateFinancialStandingRate as Cmd;

/**
 * Update Financial Standing Rate
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class UpdateFinancialStandingRate extends AbstractCommandHandler
{
    use FinancialStandingRateRulesTrait;

    protected $repoServiceName = 'FinancialStandingRate';

    public function handleCommand(CommandInterface $command)
    {
        $rate = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $this->checkInputRules($command);
        $this->checkForDuplicate($command, $rate->getId());

        $rate
            ->setGoodsOrPsv($this->getRepo()->getRefdataReference($command->getGoodsOrPsv()))
            ->setLicenceType($this->getRepo()->getRefdataReference($command->getLicenceType()))
            ->setVehicleType($this->getRepo()->getRefdataReference($command->getVehicleType()))
            ->setFirstVehicleRate($command->getFirstVehicleRate())
            ->setAdditionalVehicleRate($command->getAdditionalVehicleRate())
            ->setEffectiveFrom(new \DateTime($command->getEffectiveFrom()));

        $this->getRepo()->save($rate);

        $result = new Result();
        $result->addId('financialStandingRate', $rate->getId());
        $result->addMessage('Financial Standing Rate updated');

        return $result;
    }

    private function checkForDuplicate(CommandInterface $command, $id)
    {
        $existing = $this->getRepo()->fetchByCategoryTypeAndDate(
            $command->getGoodsOrPsv(),
            $command->getLicenceType(),
            $command->getVehicleType(),
            $command->getEffectiveFrom()
        );

        // Unset the current record, so we can count the others
        foreach ($existing as $key => $rate) {
            if ($rate->getId() == $id) {
                unset($existing[$key]);
                break;
            }
        }

        if (!empty($existing)) {
            // duplicate detected
            $msg = 'A rate for this operator type, licence type, vehicle type and effective date already exists';
            throw new ValidationException([$msg]);
        }
    }
}
