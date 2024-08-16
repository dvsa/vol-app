<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContactDetail\PhoneContact;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * @author Dmitry Golubev <dmitrijs.golubevs@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'PhoneContact';

    /**
     * Process handler
     *
     * @param \Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\GetList $query Query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var $repo \Dvsa\Olcs\Api\Domain\Repository\PhoneContact */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                ['phoneContactType']
            ),
            'count' => $repo->fetchCount($query),
        ];
    }
}
