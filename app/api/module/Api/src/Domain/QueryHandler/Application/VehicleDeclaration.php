<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * VehicleDeclaration
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class VehicleDeclaration extends AbstractQueryHandler
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
                    'trafficArea'
                ]
            ]
        );
    }
}
