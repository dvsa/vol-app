<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create BusReg Fee
 */
final class CreateBusFee extends AbstractCommandHandler
{
    protected $repoServiceName = 'Bus';

    protected $extraRepos = ['FeeType'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var BusReg $busReg */
        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $trafficArea = $busReg->getLicence()->getTrafficArea();

        $feeTrafficArea = null;

        if ($trafficArea->getIsScotland()) {
            $feeTrafficArea = TrafficAreaEntity::SCOTTISH_TRAFFIC_AREA_CODE;
        }

        $receivedDate = $busReg->getReceivedDate();

        if (!$receivedDate instanceof \DateTime) {
            $receivedDate = new \DateTime($receivedDate);
        }

        $feeType = $this->getRepo('FeeType')->fetchLatest(
            $this->getRepo()->getRefdataReference(
                ($busReg->getVariationNo()) ? FeeTypeEntity::FEE_TYPE_BUSVAR : FeeTypeEntity::FEE_TYPE_BUSAPP
            ),
            $busReg->getLicence()->getGoodsOrPsv(),
            $busReg->getLicence()->getLicenceType(),
            $receivedDate,
            $feeTrafficArea
        );

        $data = [
            'busReg' => $busReg->getId(),
            'licence' => $busReg->getLicence()->getId(),
            'invoicedDate' => date('Y-m-d'),
            'description' => $feeType->getDescription() . ' ' . $busReg->getRegNo() . ' V' . $busReg->getVariationNo(),
            'feeType' => $feeType->getId(),
            'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
            'amount' => $feeType->getFixedValue()
        ];

        return $this->handleSideEffect(CreateFee::create($data));
    }
}
