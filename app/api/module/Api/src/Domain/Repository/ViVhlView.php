<?php

/**
 * VI Vehicle repo
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\View\ViVhlView as Entity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * VI Vehicle repo
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViVhlView extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch all records for export
     *
     * @return array
     */
    public function fetchForExport()
    {
        $qb = $this->createQueryBuilder()
            ->select($this->alias . '.viLine as line')
            ->addSelect($this->alias . '.licId')
            ->addSelect($this->alias . '.vhlId');

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Clear Licence Vehicles VI indicators
     *
     * @param array $params
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function clearLicenceVehiclesViIndicators($params)
    {
        // @note not a good solution, prefer to pass all ids to SP and execute everything on server side
        foreach ($params as $recordParams) {
            try {
                $this->getDbQueryManager()
                    ->get('ViStoredProcedures\ViVhlComplete')
                    ->execute(
                        [
                            'licenceId' => $recordParams['licId'],
                            'vehicleId' => $recordParams['vhlId']
                        ]
                    );
            } catch (\Exception) {
                throw new RuntimeException('Error clearing VI flags for Operating Centres');
            }
        }
    }
}
