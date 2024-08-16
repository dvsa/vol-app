<?php

/**
 * EbsrSubmission List
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as Repository;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\Query\Bus\EbsrSubmissionList as ListDto;
use Doctrine\ORM\Query;

/**
 * EbsrSubmissionList
 * This QueryHandler will query one of two tables.
 * Either the EbsrSubmission table (for LAs) or the EbsrSubmission table (operators/organisation users).
 */
class EbsrSubmissionList extends AbstractQueryHandler
{
    protected $repoServiceName = 'EbsrSubmission';

    /**
     * @var ListDto
     */
    private $listDto;

    /**
     * handle query to retrieve a list of EBSR submissions
     *
     * @param QueryInterface $query the query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Repository $repo */
        $repo = $this->getRepo();

        // get data from transfer query
        $data = $query->getArrayCopy();

        $data['organisation'] = $this->getCurrentOrganisation()->getId();

        if (!empty($data['status']) && isset(EbsrSubmissionEntity::$displayStatus[$data['status']])) {
            $data['status'] = EbsrSubmissionEntity::$displayStatus[$data['status']];
        } else {
            $data['status'] = EbsrSubmissionEntity::$displayStatus['all_valid'];
        }

        $this->listDto = ListDto::create($data);
        $results = $repo->fetchList($this->listDto, Query::HYDRATE_OBJECT);

        return [
            'results' => $this->resultList(
                $results,
                [
                    'busReg' => [
                        'licence' => [
                            'organisation'
                        ],
                        'otherServices',
                        'status'
                    ],
                    'document'
                ]
            ),
            'count' => $repo->fetchCount($this->listDto)
        ];
    }

    /**
     * Gets the list Dto that was used (gets round problem with UT comparing objects)
     *
     * @return ListDto
     */
    public function getListDto()
    {
        return $this->listDto;
    }
}
