<?php

/**
 * VI Operating Centre repo
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\View\ViOcView as Entity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * VI Operating Centre repo
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViOcView extends AbstractRepository
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
            ->select($this->alias . '.placeholder as line')
            ->addSelect($this->alias . '.ocId');

        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Clear Operating Centres VI indicators
     *
     * @param array $params
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function clearOcViIndicators($params)
    {
        // @note not a good solution, prefer to pass all ids to SP and execute everything on server side
        foreach ($params as $recordParams) {
            try {
                $this->getDbQueryManager()
                    ->get('ViStoredProcedures\ViOcComplete')
                    ->execute(
                        [
                            'operatingCentreId' => $recordParams['ocId']
                        ]
                    );
            } catch (\Exception) {
                throw new RuntimeException('Error clearing VI flags for Operating Centres');
            }
        }
    }
}
