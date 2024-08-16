<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

class OperatorLicenceReviewService extends AbstractReviewService
{
    /**
     * @param Surrender $surrender
     *
     * @return array|mixed
     */
    public function getConfigFromData(Surrender $surrender)
    {

        $items = [];

        $items[] =
            [
                'label' => 'surrender-review-documentation-operator-licence',
                'value' => $surrender->getLicenceDocumentStatus()->getDescription()
            ];

        if ($surrender->getLicenceDocumentStatus()->getId() !== RefData::SURRENDER_DOC_STATUS_DESTROYED) {
            $items[] = [
                'label' => 'surrender-review-additional-information',
                'value' => $surrender->getLicenceDocumentInfo()
            ];
        }
        return [
            'multiItems' => [
                $items
            ]
        ];
    }
}
