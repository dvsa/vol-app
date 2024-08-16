<?php

/**
 * TxcInbox List
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\Query\Bus\TxcInboxList as ListDto;
use Dvsa\Olcs\Api\Domain\Repository\TxcInbox as TxcInboxRepo;
use Doctrine\ORM\Query;

/**
 * TxcInboxList
 * This QueryHandler will query one of two tables.
 * Either the TxcInbox table (for LAs) or the EbsrSubmission table (operators/organisation users).
 */
class TxcInboxList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TxcInbox';

    /**
     * handle query to retrieve a list of TXC inbox records
     *
     * @param QueryInterface $query the query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var TxcInboxRepo $repo */
        $repo = $this->getRepo();

        // get data from transfer query
        $data = $query->getArrayCopy();

        $data['localAuthority'] = $this->getCurrentUser()->getLocalAuthority()->getId();

        $listDto = ListDto::create($data);

        $results = $repo->fetchList($listDto, Query::HYDRATE_OBJECT);

        return [
            'result' => $this->resultList(
                $results,
                [
                    'busReg' => [
                        'ebsrSubmissions' => [
                            'ebsrSubmissionType',
                            'ebsrSubmissionStatus'
                        ],
                        'licence' => [
                            'organisation'
                        ],
                        'otherServices',
                        'status'
                    ]
                ]
            ),
            'count' => $repo->fetchCount($listDto)
        ];
    }
}
