<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContactDetail;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get a list of countries
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CountryList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Country';

    public function handleQuery(QueryInterface $query)
    {
        /* @var $repo \Dvsa\Olcs\Api\Domain\Repository\Country */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList($repo->fetchList($query, Query::HYDRATE_OBJECT), ['constraints']),
            'count' => $repo->fetchCount($query)
        ];
    }
}
