<?php

/**
 * Update Service Details
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusRegOtherService;
use Dvsa\Olcs\Api\Entity\Bus\BusServiceType as BusServiceTypeEntity;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceDetails as UpdateServiceDetailsCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\CreateBusFee as CmdCreateBusFee;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Service Details
 */
final class UpdateServiceDetails extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Bus';

    protected $extraRepos = ['BusRegOtherService'];

    protected $filteredValues = [
        null,
        '',
    ];

    /**
     * Handle command
     *
     * @param CommandInterface $command Command
     *
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateServiceDetailsCmd $command */
        /** @var BusReg $busReg */

        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $busRegId = $busReg->getId();

        $busReg->updateServiceDetails(
            $command->getServiceNo(),
            $command->getStartPoint(),
            $command->getFinishPoint(),
            $command->getVia(),
            $command->getOtherDetails(),
            $command->getReceivedDate(),
            $command->getEffectiveDate(),
            $command->getEndDate(),
            $this->getRepo()->getReference(BusNoticePeriodEntity::class, $command->getBusNoticePeriod())
        );

        $serviceTypes = $this->processServiceTypes($command->getBusServiceTypes());
        $busReg->setBusServiceTypes($serviceTypes);

        $this->getRepo()->save($busReg);

        $this->processOtherServiceNumbers($busReg, $command->getOtherServices());

        if ($busReg->shouldCreateFee()) {
            $this->result->merge($this->handleSideEffect($this->createBusFeeCommand($busRegId)));
        }

        $this->result->addId('BusReg', $busRegId);
        $this->result->addMessage('Bus registration saved successfully');

        return $this->result;
    }

    /**
     * Process other service numbers
     *
     * @param BusReg $busReg              Bus reg
     * @param array  $otherServiceNumbers Other service numbers
     *
     * @return void
     */
    private function processOtherServiceNumbers(BusReg $busReg, array $otherServiceNumbers)
    {
        $idsOfOtherServiceNumbers = [];

        if (!empty($otherServiceNumbers)) {
            foreach ($otherServiceNumbers as $serviceNumber) {
                if (in_array($serviceNumber['serviceNo'], $this->filteredValues, true)) {
                    // filter out empty values (but allow a zero)
                    continue;
                }

                if (!empty($serviceNumber['id'])) {
                    // update
                    /** @var BusRegOtherService $otherServiceEntity */
                    $otherServiceEntity = $this->getRepo('BusRegOtherService')->fetchById($serviceNumber['id']);
                    $otherServiceEntity->setServiceNo($serviceNumber['serviceNo']);
                } else {
                    // create
                    $otherServiceEntity = new BusRegOtherService($busReg, $serviceNumber['serviceNo']);
                }

                $this->getRepo('BusRegOtherService')->save($otherServiceEntity);

                $this->result->addId('BusRegOtherService', $otherServiceEntity->getId(), true);
                $this->result->addMessage('Other Bus Service/s saved successfully');
                $idsOfOtherServiceNumbers[] = $otherServiceEntity->getId();
            }

            // remove the remaining records
            foreach ($busReg->getOtherServices() as $otherServiceEntity) {
                if (!in_array($otherServiceEntity->getId(), $idsOfOtherServiceNumbers)) {
                    $this->getRepo('BusRegOtherService')->delete($otherServiceEntity);
                }
            }
        }
    }

    /**
     * Returns collection of service types.
     *
     * @param array $serviceTypes Service types
     *
     * @return ArrayCollection
     */
    private function processServiceTypes($serviceTypes)
    {
        $result = new ArrayCollection();
        if (!empty($serviceTypes)) {
            foreach ($serviceTypes as $serviceType) {
                $result->add($this->getRepo()->getReference(BusServiceTypeEntity::class, $serviceType));
            }
        }
        return $result;
    }

    /**
     * Create BusFee command
     *
     * @param int $busRegId BusReg id
     *
     * @return CmdCreateBusFee
     */
    private function createBusFeeCommand($busRegId)
    {
        return CmdCreateBusFee::create(['id' => $busRegId]);
    }
}
