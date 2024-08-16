<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\EnvironmentalComplaint;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * EnvironmentalComplaint
 */
final class EnvironmentalComplaint extends AbstractQueryHandler
{
    protected $repoServiceName = 'Complaint';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'complainantContactDetails' => [
                    'person',
                    'address' => [
                        'countryCode'
                    ]
                ],
                'operatingCentres'
            ]
        );
    }
}
