<?php

/**
 * Transport Manager Previous Licence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;

/**
 * Transport Manager Previous Licence Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerPreviousLicenceReviewService extends AbstractReviewService
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
                    'mainItems' => $this->formatLicences($tma)
                ]
            ]
        ];
    }

    private function formatLicences(TransportManagerApplication $tma)
    {
        if ($tma->getTransportManager()->getOtherLicences()->isEmpty()) {
            return [
                [
                    'freetext' => $this->translate('tm-review-previous-licence-none')
                ]
            ];
        }

        $mainItems = [];

        /** @var OtherLicence $licence */
        foreach ($tma->getTransportManager()->getOtherLicences() as $licence) {
            $mainItems[] = [
                'header' => $licence->getLicNo(),
                'multiItems' => [
                    [
                        [
                            'label' => 'tm-review-previous-licence-licNo',
                            'value' => $licence->getLicNo()
                        ],
                        [
                            'label' => 'tm-review-previous-licence-holder',
                            'value' => $licence->getHolderName()
                        ]
                    ]
                ]
            ];
        }

        return $mainItems;
    }
}
