<?php

/**
 * Licence Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LicenceOperatingCentre;

use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre as LicenceOperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Licence Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentre extends AbstractQueryHandler
{
    protected $repoServiceName = 'LicenceOperatingCentre';

    /**
     * Handle query
     *
     * @param QueryInterface $query DTO
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var LicenceOperatingCentreEntity $loc */
        $loc = $this->getRepo()->fetchUsingId($query);

        $licence = $loc->getLicence();

        $data = [
            'isPsv' => $licence->isPsv(),
            'canUpdateAddress' => $this->canUpdateAddress($query),
            'wouldIncreaseRequireAdditionalAdvertisement' => $query->getIsVariation(),
            'currentVehiclesRequired' => $loc->getNoOfVehiclesRequired(),
            'currentTrailersRequired' => $loc->getNoOfTrailersRequired(),
            'niFlag' => $licence->getNiFlag(),
        ];

        $bundle = [
            'operatingCentre' => [
                'address' => [
                    'countryCode'
                ],
                'adDocuments' => [
                    'application'
                ]
            ]
        ];

        if ($this->isReadOnlyInternalUser()) {
            $bundle = [
                'operatingCentre' => [
                    'address' => [
                        'countryCode'
                    ],
                ]
            ];
        }

        return $this->result(
            $loc,
            $bundle,
            $data
        );
    }

    /**
     * Can the address be updated
     *
     * @param QueryInterface $query DTO query
     *
     * @return bool
     */
    protected function canUpdateAddress($query)
    {
        if ($this->isGranted(Permission::INTERNAL_USER)) {
            return true;
        }

        return !$query->getIsVariation();
    }
}
