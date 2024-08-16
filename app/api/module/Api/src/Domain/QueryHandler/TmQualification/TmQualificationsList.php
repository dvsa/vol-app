<?php

/**
 * TmQualifications List
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TmQualification;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * TmQualifications List
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmQualificationsList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TmQualification';
    protected $extraRepos = ['Document', 'TransportManager'];

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $transportManager = $this->getRepo('TransportManager')->fetchById($query->getTransportManager());
        $documents = $this->getRepo('Document')->fetchListForTm($query->getTransportManager());
        return [
            'result'    => $this->resultList(
                $this->getRepo()->fetchList($query, Query::HYDRATE_OBJECT),
                ['countryCode']
            ),
            'count'     => $this->getRepo()->fetchCount($query),
            'documents' => $this->resultList($documents),
            'transportManager' => $this->result($transportManager)
        ];
    }
}
