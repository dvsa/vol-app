<?php

/**
 * Application Addresses Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;

/**
 * Application Addresses Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationAddressesReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param array $data
     * @return array
     */
    public function getConfigFromData(array $data = [])
    {
        $phoneContacts = $data['licence']['correspondenceCd']['phoneContacts'];

        $config = [
            'subSections' => [
                [
                    'mainItems' => [
                        [
                            'header' => 'application-review-addresses-correspondence-title',
                            'multiItems' => [
                                [
                                    [
                                        'label' => 'application-review-addresses-fao',
                                        'value' => $data['licence']['correspondenceCd']['fao']
                                    ],
                                    [
                                        'label' => 'application-review-addresses-correspondence-address',
                                        'value' => $this->formatFullAddress(
                                            $data['licence']['correspondenceCd']['address']
                                        )
                                    ]
                                ]
                            ]
                        ],
                        [
                            'header' => 'application-review-addresses-contact-details-title',
                            'multiItems' => [
                                [
                                    [
                                        'label' => 'application-review-addresses-correspondence-primary',
                                        'value' => $this->getPhoneNumber(
                                            $phoneContacts,
                                            PhoneContact::TYPE_PRIMARY
                                        )
                                    ],
                                    [
                                        'label' => 'application-review-addresses-correspondence-secondary',
                                        'value' => $this->getPhoneNumber(
                                            $phoneContacts,
                                            PhoneContact::TYPE_SECONDARY
                                        )
                                    ],
                                    [
                                        'label' => 'application-review-addresses-correspondence-email',
                                        'value' => $data['licence']['correspondenceCd']['emailAddress']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $showEstablishmentAddress = in_array(
            $data['licenceType']['id'],
            [
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ]
        );

        if ($showEstablishmentAddress) {
            $config['subSections'][0]['mainItems'][] = [
                'header' => 'application-review-addresses-establishment-title',
                'multiItems' => [
                    [
                        [
                            'label' => 'application-review-addresses-establishment-address',
                            'value' => $this->formatFullAddress($data['licence']['establishmentCd']['address'])
                        ]
                    ]
                ]
            ];
        }

        return $config;
    }

    private function getPhoneNumber($phoneContacts, $which)
    {
        if (is_array($phoneContacts)) {
            foreach ($phoneContacts as $phoneContact) {
                if ($phoneContact['phoneContactType']['id'] === $which) {
                    return $phoneContact['phoneNumber'];
                }
            }
        }

        return '';
    }
}
