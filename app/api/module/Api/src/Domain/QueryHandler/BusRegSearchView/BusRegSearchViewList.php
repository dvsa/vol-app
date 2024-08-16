<?php

/**
 * BusReg Search View List
 *
 * @author Craig R <uk@valtech.co.uk>, Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\BusRegSearchView;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView as Repository;
use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList as ListQueryObject;
use Doctrine\ORM\Query as DoctrineQuery;
use Olcs\Logging\Log\Logger;
use Dvsa\Olcs\Api\Domain\Query\BusRegSearchView\BusRegSearchViewList as ListDtoQry;

/**
 * BusReg Search View List
 *
 * @author Craig R <uk@valtech.co.uk>
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class BusRegSearchViewList extends AbstractQueryHandler
{
    protected $repoServiceName = 'BusRegSearchView';

    /**
     * Handle query for Bus Reg Search View lists
     *
     * @param QueryInterface $query DTO query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Repository $repo */
        $repo = $this->getRepo();

        // get data from transfer query
        $data = $query->getArrayCopy();

        if ($this->isOperator()) {
            // fetch for Organisation
            $data['organisationId'] = $this->getCurrentOrganisation()->getId();
        } elseif ($this->isLocalAuthority()) {
            $data['localAuthorityId'] = $this->getCurrentUser()->getLocalAuthority()->getId();
        }

        // create new query with extra data
        $listDto = ListDtoQry::create($data);

        return [
            'result' => $this->resultList(
                $repo->fetchList($listDto, DoctrineQuery::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($listDto)
        ];
    }
}
