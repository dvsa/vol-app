<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitSector;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\Sectors as SectorsEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitSector\Create as CreateSectorQuotasCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota as IrhpPermitSectorQuotaRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as StockRepo;
use Dvsa\Olcs\Api\Domain\Repository\Sectors as SectorsRepo;
use Dvsa\Olcs\Transfer\Query\Permits\Sectors;

/**
 * Create IRHP permit sectors (called as a side effect of stock creation)
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitSectorQuota';
    protected $extraRepos = ['IrhpPermitStock', 'Sectors'];

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
         * @var StockRepo                 $irhpPermitSectorQuotaRepo
         * @var SectorsRepo               $sectorsRepo
         * @var IrhpPermitSectorQuotaRepo $irhpPermitSectorQuotaRepo
         */
        $stockRepo = $this->getRepo('IrhpPermitStock');
        $sectorsRepo = $this->getRepo('Sectors');
        $irhpPermitSectorQuotaRepo = $this->getRepo('IrhpPermitSectorQuota');

        /**
         * @var IrhpPermitStock       $stock
         * @var CreateSectorQuotasCmd $command
         */
        $stock = $stockRepo->fetchUsingId($command);

        $sectorsListQry = Sectors::create([]);
        $sectorsList = $sectorsRepo->fetchList($sectorsListQry, Query::HYDRATE_OBJECT);

        /** @var SectorsEntity $sector */
        foreach ($sectorsList as $sector) {
            $sectorQuota = IrhpPermitSectorQuota::create($sector, $stock);
            $irhpPermitSectorQuotaRepo->save($sectorQuota);
        }

        $this->result->addId('Irhp Permit Stock', $command->getId());
        $this->result->addMessage('Irhp sector quotas created for stock ' . $command->getId());

        return $this->result;
    }
}
