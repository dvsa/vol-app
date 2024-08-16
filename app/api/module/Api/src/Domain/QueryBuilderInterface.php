<?php

/**
 * Query Builder Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;

/**
 * Query Builder Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface QueryBuilderInterface
{
    public function modifyQuery(DoctrineQueryBuilder $qb);
}
