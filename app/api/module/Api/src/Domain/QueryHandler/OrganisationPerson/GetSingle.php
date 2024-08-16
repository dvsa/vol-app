<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\OrganisationPerson;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * GetSingle OrganisationPerson
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetSingle extends AbstractQueryHandler
{
    protected $repoServiceName = 'OrganisationPerson';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            ['person' => ['title']]
        );
    }
}
