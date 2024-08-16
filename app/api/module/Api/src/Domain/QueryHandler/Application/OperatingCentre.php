<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * OperatingCentres Query Handler
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatingCentre extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        /* @var $application \Dvsa\Olcs\Api\Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $application,
            [
                'licence' => [
                    'operatingCentres' => [
                        'operatingCentre' => ['address']
                    ]
                ],
                'operatingCentres' => [
                    'operatingCentre' => ['address']
                ],
            ]
        );
    }
}
