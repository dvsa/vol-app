<?php

/**
 * TransportManagers Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

/**
 * TransportManagers Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $mainItems = [];

        foreach ($data as $transportManagerApplication) {
            $mainItems[] = $this->getTmConfig($transportManagerApplication);
        }

        return $mainItems;
    }

    public function getTmConfig($data)
    {
        $tm = $data['transportManager'];
        $cd = $tm['homeCd'];
        $person = $cd['person'];

        return [
            'header' => $this->formatPersonFullName($person),
            'multiItems' => [
                [
                    [
                        'label' => 'review-transport-manager-email',
                        'value' => $cd['emailAddress']
                    ],
                    [
                        'label' => 'review-transport-manager-dob',
                        'value' => $this->formatDate($person['birthDate'])
                    ]
                ]
            ]
        ];
    }
}
