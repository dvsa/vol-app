<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Lva;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;

/**
 * Goods Vehicles
 *
 * @author Dmitrij Golubev <dmitrij.golubev@valtech.co.uk>
 */
abstract class AbstractGoodsVehiclesExport extends AbstractQueryHandler
{
    protected $repoServiceName = 'LicenceVehicle';

    /**
     * Get vehicle data from db
     *
     * @param \Dvsa\Olcs\Transfer\Query\Lva\AbstractGoodsVehicles $qb Query Builder
     *
     * @return array
     */
    protected function getData(QueryBuilder $qb)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle $repo */
        $repo = $this->getRepo();
        $iterableResult = $repo->fetchForExport($qb);

        $result = [];
        while (false !== ($row = $iterableResult->next())) {
            $row = current($row);

            $resultRow = [
                'vehicle' => [
                    'vrm' => $row['vrm'],
                    'platedWeight' => $row['platedWeight'],
                ],
                'specifiedDate' => $row['specifiedDate'],
                'removalDate' => $row['removalDate'],
            ];

            if ($row['discId'] !== null) {
                $resultRow['goodsDiscs'] = [
                    [
                        'discNo' => $row['discNo'],
                        'ceasedDate' => $row['ceasedDate'],
                    ],
                ];
            }

            $result[] = $resultRow;
        }

        return [
            'results' => $result,
            'count' => count($result),
        ];
    }
}
