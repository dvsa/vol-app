<?php

/**
 * PreviousPublicationForPi query handler
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * PreviousPublication query handler
 */
class PreviousPublication extends AbstractQueryHandler
{
    protected $repoServiceName = 'PublicationLink';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $previousPublicatioNo = $this->getRepo()->fetchPreviousPublicationNo($query);
        return $previousPublicatioNo === null ? null : $this->result($previousPublicatioNo, ['publication']);
    }
}
