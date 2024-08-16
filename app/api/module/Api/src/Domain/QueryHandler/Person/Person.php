<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Person;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Person
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Person extends AbstractQueryHandler
{
    protected $repoServiceName = 'Person';


    public function handleQuery(QueryInterface $query)
    {
        /* @var $person \Dvsa\Olcs\Api\Entity\Person\Person */
        $person = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $person,
            ['disqualifications']
        );
    }
}
