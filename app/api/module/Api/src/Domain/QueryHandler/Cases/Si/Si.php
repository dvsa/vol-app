<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Si;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Domain\Repository\SeriousInfringement as SiRepo;
use Dvsa\Olcs\Transfer\Query\Cases\Si\Si as SiQuery;

/**
 * SeriousInfringement QueryHandler
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Si extends AbstractQueryHandler
{
    protected $repoServiceName = 'SeriousInfringement';

    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var SiRepo $repo
         * @var SiEntity $si
         * @var SiQuery $query
         */
        $repo = $this->getRepo();
        $si = $repo->fetchUsingId($query);

        return $this->result(
            $si,
            [
                'memberStateCode',
                'siCategory',
                'siCategoryType',
                'appliedPenalties' => ['siPenaltyType', 'seriousInfringement', 'erruPenaltyRequested'],
                'imposedErrus' => ['executed', 'siPenaltyImposedType'],
                'requestedErrus' => ['siPenaltyRequestedType'],
                'case' => [
                    'erruRequest' => [
                        'memberStateCode'
                    ]
                ]
            ]
        );
    }
}
