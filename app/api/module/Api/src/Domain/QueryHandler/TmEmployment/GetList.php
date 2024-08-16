<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * GetList
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TmEmployment';

    protected $extraRepos = ['TransportManager'];

    public function handleQuery(QueryInterface $query)
    {
        $transportManager = $this->getRepo('TransportManager')->fetchById($query->getTransportManager());

        return [
            'result' => $this->resultList(
                $this->getRepo()->fetchList($query, \Doctrine\ORM\Query::HYDRATE_OBJECT),
                [
                    'contactDetails' => [
                        'address' => [
                            'countryCode',
                        ]
                    ]
                ]
            ),
            'count' => $this->getRepo()->fetchCount($query),
            'transportManager' => $this->result($transportManager)
        ];
    }
}
