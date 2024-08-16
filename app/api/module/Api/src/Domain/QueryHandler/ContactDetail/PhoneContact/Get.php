<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContactDetail\PhoneContact;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * @author Dmitry Golubev <dmitrijs.golubevs@valtech.co.uk>
 */
class Get extends AbstractQueryHandler
{
    protected $repoServiceName = 'PhoneContact';

    /**
     * Process handler
     *
     * @param \Dvsa\Olcs\Transfer\Query\ContactDetail\PhoneContact\Get $query Query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            ['contactDetails']
        );
    }
}
