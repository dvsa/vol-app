<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentres extends AbstractQueryHandler
{
    protected $repoServiceName = 'InspectionRequest';

    protected $extraRepos = ['Application', 'Licence'];

    /**
     * @param \Dvsa\Olcs\Transfer\Query\InspectionRequest\OperatingCentres $query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $id = $query->getIdentifier();

        if ($query->getType() === 'licence') {
            /** @var \Dvsa\Olcs\Api\Domain\Repository\Licence $repo */
            $repo = $this->getRepo('Licence');

            $entity = $repo->fetchWithOperatingCentres($id);
            $operatingCentres = $entity->getOcForInspectionRequest();
        } else {
            /** @var \Dvsa\Olcs\Api\Domain\Repository\Application $repo */
            $repo = $this->getRepo('Application');

            $entity = $repo->fetchWithLicenceAndOc($id);
            $operatingCentres = $entity->getOcForInspectionRequest();
        }

        return [
            'result' => $this->resultList(
                $operatingCentres,
                [
                    'address',
                ]
            ),
            'count' => count($operatingCentres),
        ];
    }
}
