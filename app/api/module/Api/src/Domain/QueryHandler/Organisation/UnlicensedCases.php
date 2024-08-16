<?php

/**
 * Unlicensed Organisation with Cases
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Organisation;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Unlicensed Organisation with Cases
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedCases extends AbstractQueryHandler
{
    protected $repoServiceName = 'Organisation';

    protected $extraRepos = ['Cases'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $organisation \Dvsa\Olcs\Api\Entity\Organisation\Organisation */
        $organisation = $this->getRepo()->fetchUsingId($query);

        $licenceId = $organisation->getLicences()->first()->getId();

        $caseQuery = \Dvsa\Olcs\Transfer\Query\Cases\ByLicence::create(
            [
                'licence' => $licenceId,
                'sort'    => $query->getSort(),
                'order'   => $query->getOrder(),
                'page'    => $query->getPage(),
                'limit'   => $query->getLimit(),
            ]
        );
        $cases = $this->getRepo('Cases')->fetchList($caseQuery, Query::HYDRATE_OBJECT);
        $casesCount = $this->getRepo('Cases')->fetchCount($caseQuery);

        return $this->result(
            $organisation,
            [],
            [
                'cases' => [
                    'result' => $this->resultList($cases),
                    'count' => $casesCount,
                ],
                'licenceId' => $licenceId,
            ]
        );
    }
}
