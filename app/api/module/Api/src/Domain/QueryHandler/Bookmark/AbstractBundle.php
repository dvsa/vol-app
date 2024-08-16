<?php

/**
 * Abstract Bundle Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Abstract Bundle Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractBundle extends AbstractQueryHandler
{
    public function handleQuery(QueryInterface $query)
    {
        try {
            $entity = $this->getRepo()->fetchUsingId($query);
        } catch (NotFoundException) {
            return null;
        }

        return $this->result(
            $entity,
            $query->getBundle()
        )->serialize();
    }
}
