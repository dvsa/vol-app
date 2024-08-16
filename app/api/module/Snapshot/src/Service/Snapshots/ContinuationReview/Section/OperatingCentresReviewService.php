<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Operating Centres Continuation Review Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentresReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return array
     */
    public function getConfigFromData(ContinuationDetail $continuationDetail)
    {
        $licence = $continuationDetail->getLicence();

        $locs = $licence->getOperatingCentres();
        if (count($locs) == 0) {
            return [];
        }

        $canHaveTrailers = $licence->canHaveTrailer();

        $vehiclesColumnSuffix = 'vehicles';
        if ($licence->isVehicleTypeMixedWithLgv()) {
            $vehiclesColumnSuffix = 'heavy-goods-vehicles';
        }

        $header = [
            [
                ['value' => 'continuations.oc-section.table.name', 'header' => true],
                ['value' => 'continuations.oc-section.table.' . $vehiclesColumnSuffix, 'header' => true],
            ]
        ];
        if ($canHaveTrailers) {
            $header[0][] = ['value' => 'continuations.oc-section.table.trailers', 'header' => true];
        }

        $config = [];
        /** @var LicenceOperatingCentre $loc */
        foreach ($locs as $loc) {
            /** @var OperatingCentre $oc */
            $oc = $loc->getOperatingCentre();
            $address = $oc->getAddress();
            $row = [
                ['value' => implode(', ', [$address->getAddressLine1(), $address->getTown()])],
                ['value' => $loc->getNoOfVehiclesRequired()]
            ];
            if ($canHaveTrailers) {
                $row[] = ['value' => $loc->getNoOfTrailersRequired()];
            }
            $config[] = $row;
        }
        usort(
            $config,
            fn($a, $b) => strcmp($a[0]['value'], $b[0]['value'])
        );

        return array_merge($header, $config);
    }

    /**
     * Get summary from data
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return array
     */
    public function getSummaryFromData(ContinuationDetail $continuationDetail)
    {
        $licence = $continuationDetail->getLicence();

        $mappings = [
            'totAuthVehicles' => [
                'suffix' => 'vehicles',
                'value' => $licence->getTotAuthVehicles()
            ],
            'totAuthHgvVehicles' => [
                'suffix' => 'heavy-goods-vehicles',
                'value' => $licence->getTotAuthHgvVehicles()
            ],
            'totAuthLgvVehicles' => [
                'suffix' => 'light-goods-vehicles',
                'value' => $licence->getTotAuthLgvVehicles()
            ],
            'totAuthTrailers' => [
                'suffix' => 'trailers',
                'value' => $licence->getTotAuthTrailers()
            ],
        ];

        $applicableAuthProperties = $licence->getApplicableAuthProperties();
        $summary = [];

        foreach ($applicableAuthProperties as $propertyName) {
            $mapping = $mappings[$propertyName];
            $suffix = $mapping['suffix'];
            $value = $mapping['value'];

            $summary[] = [
                ['value' => 'continuations.oc-section.table.' . $suffix, 'header' => true],
                ['value' => $value]
            ];
        }

        return $summary;
    }

    /**
     * Get summary header
     *
     * @return string
     */
    public function getSummaryHeader()
    {
        return 'continuations.oc-section.table.authorisation';
    }
}
