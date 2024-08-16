<?php

/**
 * Change Of Entity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ChangeOfEntity;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Change Of Entity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ChangeOfEntity extends AbstractQueryHandler
{
    protected $repoServiceName = 'ChangeOfEntity';

    public function handleQuery(QueryInterface $query)
    {
        $changeOfEntity = $this->getRepo()->fetchUsingId($query);

        return $this->result($changeOfEntity);
    }
}
