<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Community Licence
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class CommunityLicence extends AbstractQueryHandler
{
    protected $repoServiceName = 'CommunityLic';

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicence $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            []
        );
    }
}
