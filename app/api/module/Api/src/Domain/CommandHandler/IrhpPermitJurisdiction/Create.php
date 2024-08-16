<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitJurisdiction;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitJurisdiction\Create as CreateJurisdictionQuotasCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitJurisdictionQuota as IrhpPermitJurisdictionQuotaRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as StockRepo;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;

/**
 * Create IRHP permit devolved quotas (called as a side effect of stock creation)
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitJurisdictionQuota';
    protected $extraRepos = ['IrhpPermitStock', 'TrafficArea'];

    /**
     * Create sector quotas for a given stock
     *
     * @param CommandInterface $command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var StockRepo                       $stockRepo
         * @var TrafficAreaRepo                 $trafficAreaRepo
         * @var IrhpPermitJurisdictionQuotaRepo $irhpPermitJurisdictionQuotaRepo
         * @var IrhpPermitStock                 $stock
         * @var CreateJurisdictionQuotasCmd     $command
         */
        $stockRepo = $this->getRepo('IrhpPermitStock');
        $trafficAreaRepo = $this->getRepo('TrafficArea');
        $irhpPermitJurisdictionQuotaRepo = $this->getRepo('IrhpPermitJurisdictionQuota');

        $stock = $stockRepo->fetchUsingId($command);

        $trafficAreaList = $trafficAreaRepo->fetchDevolved();

        /** @var TrafficAreaEntity $trafficArea */
        foreach ($trafficAreaList as $trafficArea) {
            $jurisdictionQuota = IrhpPermitJurisdictionQuota::create($trafficArea, $stock);
            $irhpPermitJurisdictionQuotaRepo->save($jurisdictionQuota);
        }

        $stockId = $stock->getId();
        $this->result->addId('Irhp Permit Stock', $stockId);
        $this->result->addMessage('Irhp jurisdiction quotas created for stock ' . $stockId);

        return $this->result;
    }
}
