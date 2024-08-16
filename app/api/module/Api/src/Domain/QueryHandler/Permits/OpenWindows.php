<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitType as IrhpPermitTypeRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Transfer\Query\Permits\OpenWindows as OpenWindowsQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use DateTime;

/**
 * Open windows
 */
class OpenWindows extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitWindow';
    protected $extraRepos = ['IrhpPermitType', 'IrhpPermitStock'];
    protected $bundle = [];

    /**
     * Handle query
     *
     * @param QueryInterface|OpenWindowsQuery $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Dvsa\Olcs\Api\Domain\Exception\NotFoundException
     */
    public function handleQuery(QueryInterface $query)
    {
        $date = new DateTime();

        /** @var IrhpPermitWindowRepo $irhpPermitWindowRepo */
        $irhpPermitWindowRepo = $this->getRepo();

        /** @var IrhpPermitTypeRepo $irhpPermitTypeRepo */
        $irhpPermitTypeRepo = $this->getRepo('IrhpPermitType');

        /** @var IrhpPermitStockRepo $irhpPermitStockRepo */
        $irhpPermitStockRepo = $this->getRepo('IrhpPermitStock');

        /** @var IrhpPermitTypeEntity $irhpPermitType */
        $irhpPermitType = $irhpPermitTypeRepo->fetchById($query->getPermitType());

        $stocks = $irhpPermitStockRepo->fetchByIrhpPermitType($irhpPermitType->getId());

        /** @var IrhpPermitStockEntity $stock */
        foreach ($stocks as $stock) {
            $openWindows = $irhpPermitWindowRepo->fetchOpenWindows($stock->getId(), $date);

            if (!empty($openWindows[0])) {
                return ['windows' => $openWindows];
            }
        }

        return ['windows' => []];
    }
}
