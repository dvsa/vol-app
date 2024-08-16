<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusFeeTypeBundle as BusFeeAmountBundleQry;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;

/**
 * BusFeeTypeBundle Bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusFeeTypeBundle extends AbstractQueryHandler
{
    protected $repoServiceName = 'Bus';

    protected $extraRepos = ['FeeType'];

    /**
     * handle query
     *
     * @param QueryInterface|BusFeeAmountBundleQry $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var BusRepo $busRepo
         * @var FeeTypeRepo $feeTypeRepo
         * @var BusRegEntity $busReg
         */
        $busRepo = $this->getRepo();
        $feeTypeRepo = $this->getRepo('FeeType');

        $busReg = $busRepo->fetchUsingId($query);
        $trafficArea = $busReg->getLicence()->getTrafficArea();
        $feeTrafficArea = null;

        if ($trafficArea->getIsScotland()) {
            $feeTrafficArea = TrafficAreaEntity::SCOTTISH_TRAFFIC_AREA_CODE;
        }

        $receivedDate = $busReg->processDate($busReg->getReceivedDate());

        $feeType = $feeTypeRepo->fetchLatest(
            $feeTypeRepo->getRefdataReference(
                ($busReg->getVariationNo()) ? FeeTypeEntity::FEE_TYPE_BUSVAR : FeeTypeEntity::FEE_TYPE_BUSAPP
            ),
            $busReg->getLicence()->getGoodsOrPsv(),
            $busReg->getLicence()->getLicenceType(),
            $receivedDate,
            $feeTrafficArea
        );

        return $this->result(
            $feeType,
            $query->getBundle()
        )->serialize();
    }
}
