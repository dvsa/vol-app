<?php

/**
 * Application Addresses Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Transfer\Validators\SmsPhoneNumber;

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
    #[\Override]
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

        // only shown when there is one, as it isn't asked for until capture is on
        $smsNumber = $this->latestSmsNumber($phoneContacts);
        if ($smsNumber !== '') {
            array_splice($config['subSections'][0]['mainItems'][1]['multiItems'][0], 2, 0, [[
                'label' => 'application-review-addresses-sms-number',
                'value' => SmsPhoneNumber::formatForDisplay($smsNumber),
            ]]);
        }

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

    /**
     * The most recently changed SMS number, the same choice as SmsNumber::latestContact()
     */
    private function latestSmsNumber($phoneContacts): string
    {
        $latest = '';
        $latestKey = null;

        foreach (is_array($phoneContacts) ? $phoneContacts : [] as $phoneContact) {
            if (($phoneContact['phoneContactType']['id'] ?? null) !== PhoneContact::TYPE_SMS) {
                continue;
            }

            $number = trim((string) ($phoneContact['phoneNumber'] ?? ''));
            if ($number === '') {
                continue;
            }

            $key = [$this->changedAt($phoneContact), (int) ($phoneContact['id'] ?? 0)];
            if ($latestKey === null || $key > $latestKey) {
                $latest = $number;
                $latestKey = $key;
            }
        }

        return $latest;
    }

    private function changedAt(array $phoneContact): int
    {
        $at = $phoneContact['lastModifiedOn'] ?? $phoneContact['createdOn'] ?? null;

        if ($at instanceof \DateTimeInterface) {
            return $at->getTimestamp();
        }

        return is_string($at) ? (int) strtotime($at) : 0;
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
