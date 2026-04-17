<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Hearing Bundle Bookmark
 */
class HearingBundle extends AbstractBundle
{
    protected $repoServiceName = 'Hearing';

    /**
     * Handle query
     *
     * @param QueryInterface $query Query DTO
     *
     * @return array|null
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        try {
            $entity = $this->getRepo()->fetchOneByCase($query->getCase());
        } catch (NotFoundException) {
            return null;
        }

        return $this->result(
            $entity,
            $query->getBundle()
        )->serialize();
    }
}
