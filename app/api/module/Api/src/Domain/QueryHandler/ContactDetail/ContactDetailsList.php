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
class ContactDetailsList extends AbstractQueryHandler
{
    protected $repoServiceName = 'ContactDetails';

    /**
     * Query Handler
     *
     * @param \Dvsa\Olcs\Transfer\Query\ContactDetail\ContactDetailsList $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /* @var $repo \Dvsa\Olcs\Api\Domain\Repository\ContactDetails */
        $repo = $this->getRepo();

        $result = $repo->fetchList($query, Query::HYDRATE_OBJECT);

        $count = count($result);
        if (0 !== (int)$query->getLimit()) {
            $count = $repo->fetchCount($query);
        }

        return [
            'result' => $this->resultList($result),
            'count' => $count,
        ];
    }
}
