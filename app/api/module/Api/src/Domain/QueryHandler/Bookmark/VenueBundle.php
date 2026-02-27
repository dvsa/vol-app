<?php

/**
 * Venue Bundle Bookmark
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Venue Bundle Bookmark
 */
class VenueBundle extends AbstractBundle
{
    protected $repoServiceName = 'Venue';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            ['address']
        );
    }
}
