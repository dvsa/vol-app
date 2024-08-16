<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LicenceStatusRule;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * LicenceStatusRule
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class LicenceStatusRule extends AbstractQueryHandler
{
    protected $repoServiceName = 'LicenceStatusRule';

    /**
     * @param \Dvsa\Olcs\Transfer\Query\LicenceStatusRule\LicenceStatusRule $query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'licence' => [
                    'decisions',
                ],
                'licenceStatus',
            ]
        );
    }
}
