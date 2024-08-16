<?php

/**
 * Previous hearing query handler
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Previous hearing query handler
 */
class PreviousHearing extends AbstractQueryHandler
{
    protected $repoServiceName = 'PiHearing';

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
        $previousHearing = $this->getRepo()->fetchPreviousHearing(
            $query->getPi(),
            new \DateTime($query->getHearingDate())
        );
        return $previousHearing === null ? null : $this->result($previousHearing);
    }
}
