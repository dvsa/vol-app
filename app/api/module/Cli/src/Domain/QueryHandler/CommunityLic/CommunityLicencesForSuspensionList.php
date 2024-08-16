<?php

namespace Dvsa\Olcs\Cli\Domain\QueryHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Community Licences for suspension
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicencesForSuspensionList extends AbstractQueryHandler
{
    protected $repoServiceName = 'CommunityLic';

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicences $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $result = $this->getRepo()->fetchForSuspension($query->getDate());
        return [
            'result' => $this->resultList($result),
            'count' => count($result)
        ];
    }
}
