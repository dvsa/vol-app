<?php

/**
 * Note
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Processing;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Note as NoteRepository;

/**
 * Note
 */
class Note extends AbstractQueryHandler
{
    protected $repoServiceName = 'Note';

    public function handleQuery(QueryInterface $query)
    {
        /** @var NoteRepository $repo */
        $repo = $this->getRepo();

        return $this->result($repo->fetchUsingId($query));
    }
}
