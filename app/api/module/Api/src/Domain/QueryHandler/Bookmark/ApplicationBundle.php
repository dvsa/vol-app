<?php

/**
 * Application Bundle Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Application Bundle Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBundle extends AbstractBundle
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Cases'];

    /**
     * Handle the query
     *
     * @param QueryInterface $query Query DTO
     *
     * @return array|null
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $entity = null;
        try {
            if (!empty($query->getId())) {
                $entity = $this->getRepo()->fetchUsingId($query);
            }
            if (!empty($query->getCase())) {
                $case = $this->getRepo('Cases')->fetchById($query->getCase());
                $entity = $case->getApplication();
            }
        } catch (NotFoundException) {
            return null;
        }

        if (!$entity) {
            return null;
        }

        return $this->result(
            $entity,
            $query->getBundle()
        )->serialize();
    }
}
