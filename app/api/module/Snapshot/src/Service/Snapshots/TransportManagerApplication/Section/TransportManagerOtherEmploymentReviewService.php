<?php

/**
 * Transport Manager Other Employment Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Tm\TmEmployment;

/**
 * Transport Manager Other Employment Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerOtherEmploymentReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfig(TransportManagerApplication $tma)
    {
        return [
            'subSections' => [
                [
                    'mainItems' => $this->formatOtherEmployments($tma)
                ]
            ]
        ];
    }

    private function formatOtherEmployments(TransportManagerApplication $tma)
    {
        if ($tma->getTransportManager()->getEmployments()->isEmpty()) {
            return [
                [
                    'freetext' => $this->translate('tm-review-other-employment-none')
                ]
            ];
        }

        $mainItems = [];

        /** @var TmEmployment $employment */
        foreach ($tma->getTransportManager()->getEmployments() as $employment) {
            $mainItems[] = [
                'header' => $employment->getEmployerName(),
                'multiItems' => [
                    [
                        [
                            'label' => 'tm-review-other-employment-address',
                            'value' => $this->formatFullAddress($employment->getContactDetails()->getAddress())
                        ],
                        [
                            'label' => 'tm-review-other-employment-position',
                            'value' => $employment->getPosition()
                        ],
                        [
                            'label' => 'tm-review-other-employment-hours-per-week',
                            'value' => $employment->getHoursPerWeek()
                        ],
                        [
                            'label' => 'tm-review-other-employment-confirmation',
                            'value' => $this->translate('transportManager.data.availability.understoodAvailabilityAgreementConfirmation')
                        ],
                    ]
                ]
            ];
        }

        return $mainItems;
    }
}
