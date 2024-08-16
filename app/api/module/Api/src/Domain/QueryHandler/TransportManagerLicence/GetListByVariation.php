<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerLicence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Get a list of Transport Manager Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GetListByVariation extends AbstractQueryHandler
{
    protected $repoServiceName = 'TransportManagerLicence';

    protected $extraRepos = ['Application'];

    /**
     * handle query
     *
     * @param QueryInterface $query DTO
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Entity\Application\Application $application */
        $application = $this->getRepo('Application')->fetchById($query->getVariation());
        $result = $this->getRepo()->fetchByLicence($application->getLicence()->getId());

        return [
            'result' => $this->resultList(
                $result,
                [
                    'licence' => [
                        'status',
                        'licenceType',
                    ],
                    'transportManager' => [
                        'homeCd' => [
                            'person',
                        ],
                        'tmType',
                    ]
                ]
            ),
            'count' => count($result),
            'requiresSiQualification' =>
                $application->getLicenceType()->getId() === Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
        ];
    }
}
