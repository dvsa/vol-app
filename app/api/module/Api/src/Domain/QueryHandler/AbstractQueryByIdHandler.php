<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler;

use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Retrieve a record by id
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class AbstractQueryByIdHandler extends AbstractQueryHandler
{
    protected $bundle = [];
    protected $values = [];

    public function handleQuery(QueryInterface $query)
    {
        $recordObject = $this->getRepo()->fetchUsingId($query);
        return $this->result($recordObject, $this->bundle, $this->values);
    }
}
