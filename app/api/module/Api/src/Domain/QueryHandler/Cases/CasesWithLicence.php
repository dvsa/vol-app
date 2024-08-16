<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Cases with licence information attached. Specifically Licence Operating centres.
 * Used to generate the 'attachedTo field on conditions and uni
 */
final class CasesWithLicence extends AbstractQueryHandler
{
    protected $repoServiceName = 'Cases';

    public function handleQuery(QueryInterface $query)
    {
        $case = $this->getRepo()->fetchWithLicenceUsingId($query);

        return $this->result(
            $case,
            [
                'licence' => [
                    'operatingCentres' => [
                        'operatingCentre' => [
                            'address'
                        ]
                    ]
                ]
            ]
        );
    }
}
