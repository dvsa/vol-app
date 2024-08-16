<?php

/**
 * Transport Manager Previous Conviction Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction;

/**
 * Transport Manager Previous Conviction Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerPreviousConvictionReviewService extends AbstractReviewService
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
                    'mainItems' => $this->formatConvictions($tma)
                ]
            ]
        ];
    }

    private function formatConvictions(TransportManagerApplication $tma)
    {
        if ($tma->getTransportManager()->getPreviousConvictions()->isEmpty()) {
            return [
                [
                    'freetext' => $this->translate('tm-review-previous-conviction-none')
                ]
            ];
        }

        $mainItems = [];

        /** @var PreviousConviction $conviction */
        foreach ($tma->getTransportManager()->getPreviousConvictions() as $conviction) {
            $mainItems[] = [
                'header' => $conviction->getCategoryText(),
                'multiItems' => [
                    [
                        [
                            'label' => 'tm-review-previous-conviction-date',
                            'value' => $this->formatDate($conviction->getConvictionDate())
                        ],
                        [
                            'label' => 'tm-review-previous-conviction-offence',
                            'value' => $conviction->getCategoryText()
                        ],
                        [
                            'label' => 'tm-review-previous-conviction-offence-details',
                            'value' => $conviction->getNotes()
                        ],
                        [
                            'label' => 'tm-review-previous-conviction-court',
                            'value' => $conviction->getCourtFpn()
                        ],
                        [
                            'label' => 'tm-review-previous-conviction-penalty',
                            'value' => $conviction->getPenalty()
                        ]
                    ]
                ]
            ];
        }

        return $mainItems;
    }
}
