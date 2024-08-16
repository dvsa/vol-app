<?php

/**
 * Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Workshop;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Workshop extends AbstractQueryHandler
{
    protected $repoServiceName = 'Workshop';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'contactDetails' => [
                    'address' => ['countryCode']
                ]
            ]
        );
    }
}
