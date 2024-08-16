<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a Transport Manager Application for Responsibilities page
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetForResponsibilities extends AbstractQueryHandler
{
    protected $repoServiceName = 'TransportManagerApplication';

    protected $extraRepos = [
        'OtherLicence'
    ];

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetForResponsibilities $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication $repo */
        $repo = $this->getRepo();

        $transportManagerApplication = $repo->fetchForResponsibilities($query->getId());

        return $this->result(
            $transportManagerApplication,
            [
                'application' => [
                    'licence' => [
                        'organisation'
                    ]
                ],
                'operatingCentres',
                'otherLicences' => [
                    'role'
                ]
            ]
        );
    }
}
