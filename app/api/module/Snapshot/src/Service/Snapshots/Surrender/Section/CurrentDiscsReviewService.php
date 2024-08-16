<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Surrender;

class CurrentDiscsReviewService extends AbstractReviewService
{
    /**
     * @param Surrender $surrender
     *
     * @return array
     */
    public function getConfigFromData(Surrender $surrender)
    {
        $items = [];

        $items[] =
            [
                'label' => 'surrender-review-current-discs-destroyed',
                'value' => $surrender->getDiscDestroyed() ?? 0
            ];

        $items[] =
            [
                'label' => 'surrender-review-current-discs-lost',
                'value' => $surrender->getDiscLost() ?? 0
            ];

        if ($surrender->getDiscLost() !== null) {
            $items[] =
                [
                    'label' => 'surrender-review-additional-information',
                    'value' => $surrender->getDiscLostInfo()
                ];
        }

        $items[] = [

            'label' => 'surrender-review-current-discs-stolen',
            'value' => $surrender->getDiscStolen() ?? 0

        ];

        if ($surrender->getDiscStolen() !== null) {
            $items[] = [

                'label' => 'surrender-review-additional-information',
                'value' => $surrender->getDiscStolenInfo()

            ];
        }

        return [
            'multiItems' => [
                $items
            ]
        ];
    }
}
