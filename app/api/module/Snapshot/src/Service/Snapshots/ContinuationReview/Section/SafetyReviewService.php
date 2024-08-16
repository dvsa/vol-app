<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\Workshop;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Safety Continuation Review Service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SafetyReviewService extends AbstractReviewService
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
        /** @var Licence $licence */
        $licence = $continuationDetail->getLicence();
        $workshops = $licence->getWorkshops();

        $header = [
            [
                ['value' => 'continuations.safety-section.table.inspector', 'header' => true],
                ['value' => 'continuations.safety-section.table.address', 'header' => true],
            ]
        ];

        $config = [];
        /** @var Workshop $workshop */
        foreach ($workshops as $workshop) {
            /** @var ContactDetails $contactDetails */
            $contactDetails = $workshop->getContactDetails();
            /** @var Address $address */
            $address = $contactDetails->getAddress();
            $row = [
                [
                    'value' => $contactDetails->getFao()
                        . ' ('
                        . (($workshop->getIsExternal() === 'Y')
                            ? $this->translate('continuations.safety-section.table.external-contractor')
                            : $this->translate('continuations.safety-section.table.owner-or-employee'))
                        . ')',
                ],
                ['value' => implode(', ', [$address->getAddressLine1(), $address->getTown()])]
            ];
            $config[] = $row;
        }
        usort(
            $config,
            fn($a, $b) => strcmp($a[0]['value'], $b[0]['value'])
        );
        return (count($config) === 0)
            ? ['emptyTableMessage' => $this->translate('There are no safety inspectors recorded on your licence')]
            : array_merge($header, $config);
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
        /** @var Licence $licence */
        $licence = $continuationDetail->getLicence();

        $safetyInsVehicles = null;
        if (!empty($licence->getSafetyInsVehicles())) {
            $safetyInsVehicles = $licence->getSafetyInsVehicles()
                . ' '
                . (
                ((int) $licence->getSafetyInsVehicles() === 1)
                    ? $this->translate('continuations.safety-section.table.week')
                    : $this->translate('continuations.safety-section.table.weeks')
                );
        }

        $summary = [
            [
                [
                    'value' => $this->translate('continuations.safety-section.table.max-time-vehicles'),
                    'header' => true
                ],
                [
                    'value' => $safetyInsVehicles ?? $this->translate('continuations.safety-section.table.not-known'),
                ]
            ]
        ];

        if ($licence->canHaveTrailer()) {
            $safetyInsTrailers = null;
            if (!empty($licence->getSafetyInsTrailers())) {
                $safetyInsTrailers = $licence->getSafetyInsTrailers()
                    . ' '
                    . (
                    ((int) $licence->getSafetyInsTrailers() === 1)
                        ? $this->translate('continuations.safety-section.table.week')
                        : $this->translate('continuations.safety-section.table.weeks')
                    );
            }

            $summary[] = [
                [
                    'value' => $this->translate('continuations.safety-section.table.max-time-trailers'),
                    'header' => true
                ],
                [
                    'value' => $safetyInsTrailers ?? $this->translate('continuations.safety-section.table.not-known'),
                ]
            ];
        }

        $variesKey = 'continuations.safety-section.table.varies';
        if (!$licence->canHaveTrailer()) {
            $variesKey .= '.no-trailers';
        }

        $safetyInsVaries = null;
        if ($licence->getSafetyInsVaries() !== null) {
            $safetyInsVaries = ($licence->getSafetyInsVaries() === 'Y')
                ? $this->translate('Yes')
                : $this->translate('No');
        }

        $summary[] = [
            [
                'value' => $this->translate($variesKey),
                'header' => true
            ],
            [
                'value' => $safetyInsVaries ?? $this->translate('continuations.safety-section.table.not-known'),
            ]
        ];

        $tachographIns = null;
        if ($licence->getTachographIns() !== null) {
            $tachographIns = $licence->getTachographIns()->getId();
        }

        $summary[] = [
            [
                'value' => $this->translate('continuations.safety-section.table.tachographs'),
                'header' => true
            ],
            [
                'value' => $tachographIns !== null
                    ? $this->translate('continuations.safety-section.table.' . $tachographIns)
                    : $this->translate('continuations.safety-section.table.not-known'),
            ]
        ];

        if ($tachographIns === Licence::TACH_EXT) {
            $summary[] = [
                [
                    'value' => $this->translate('continuations.safety-section.table.tachographInsName'),
                    'header' => true
                ],
                [
                    'value' => !empty($licence->getTachographInsName())
                        ? $licence->getTachographInsName()
                        : $this->translate('continuations.safety-section.table.not-known'),
                ]
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
        return 'continuations.safety-details.label';
    }
}
