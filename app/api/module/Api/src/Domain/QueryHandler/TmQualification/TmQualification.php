<?php

/**
 * TmQualification
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TmQualification;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;

/**
 * TmQualification
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmQualification extends AbstractQueryHandler
{
    protected $repoServiceName = 'TmQualification';

    public function handleQuery(QueryInterface $query)
    {
        /** @var TmQualificationRepo $repo */
        $repo = $this->getRepo();
        $tmQualification = $repo->fetchUsingId($query);

        return $this->result(
            $tmQualification,
            [
                'qualificationType',
                'countryCode',
                'transportManager'
            ]
        );
    }
}
