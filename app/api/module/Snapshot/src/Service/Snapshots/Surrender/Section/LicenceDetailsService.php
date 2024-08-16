<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;

class LicenceDetailsService extends AbstractReviewService
{
    /**
     * @param Licence $data
     * @return mixed
     */
    public function getConfigFromData(Surrender $surrender)
    {
        $licence = $surrender->getLicence();
        return [
            'multiItems' => [
                [
                    [
                        'label' => 'surrender-review-licence-number',
                        'value' => $licence->getLicNo()
                    ],
                    [
                        'label' => 'surrender-review-licence-holder',
                        'value' => $licence->getOrganisation()->getName()
                    ],
                    [
                        'label' => 'surrender-review-licence-trading-name',
                        'value' => $licence->getTradingName()
                    ],
                    [
                        'label' => 'surrender-review-licence-correspondence-address',
                        'value' => $this->formatFullAddress($licence->getCorrespondenceCd()->getAddress())
                    ]
                ]
            ]
        ];
    }
}
