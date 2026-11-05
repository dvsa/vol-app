<?php

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\Addresses;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Data\Mapper\Lva\Addresses::class)]
final class AddressesTest extends MockeryTestCase
{
    /** @var  array */
    private $apiData;

    /** @var  array */
    private $formData;

    #[\Override]
    protected function setUp(): void
    {
        $this->apiData = [
            'correspondenceCd' => [
                'id' => 'unit_CdId',
                'version' => 'unit_CdVer',
                'fao' => 'unit_CdFao',
                'address' => [
                    'addressLine1' => 'unit_CdAdrLine1',
                    'countryCode' => [
                        'id' => 'unit_CdAdrCountryId',
                    ],
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => [
                            'id' => 'phone_t_primary',
                        ],
                        'phoneNumber' => 'unit_CdPhoneNr1',
                        'id' => 'unit_CdPhoneId1',
                        'version' => 'unit_CdPhoneVersion',
                    ],
                ],
                'emailAddress' => 'unit_CdEmail',
            ],
            'establishmentCd' => [
                'address' => [
                    'addressLine1' => 'unit_EstAdrLine1',
                    'countryCode' => [
                        'id' => 'unit_EstAdrCountryId',
                    ],
                ],
                'id' => 'unit_EstId',
                'version' => 'unit_EstVer',
                'fao' => 'unit_EstFao',
            ],
            'transportConsultantCd' => [
                'writtenPermissionToEngage' => 'unit_TcPerm',
                'fao' => 'unit_TcFao',
                'address' => [
                    'addressLine1' => 'unit_EstAdrLine1',
                    'countryCode' => [
                        'id' => 'unit_EstAdrCountryId',
                    ],
                ],
                'phoneContacts' => [
                    [
                        'phoneContactType' => [
                            'id' => 'phone_t_primary',
                        ],
                        'phoneNumber' => 'unit_TrPhoneNr1',
                        'id' => 'unit_TrPhoneId1',
                        'version' => 'unit_TrPhoneVersion',
                    ],
                ],
                'emailAddress' => 'unit_TrEmail',
            ],
        ];

        //  match to api data
        $this->formData = [
            'correspondence' => [
                'id' => 'unit_CdId',
                'version' => 'unit_CdVer',
                'fao' => 'unit_CdFao',
            ],
            'contact' => [
                'phone_primary' => 'unit_CdPhoneNr1',
                'phone_primary_id' => 'unit_CdPhoneId1',
                'phone_primary_version' => 'unit_CdPhoneVersion',
                'email' => 'unit_CdEmail',
            ],
            'correspondence_address' => [
                'addressLine1' => 'unit_CdAdrLine1',
                'countryCode' => 'unit_CdAdrCountryId',
            ],
            'establishment' => [
                'id' => 'unit_EstId',
                'version' => 'unit_EstVer',
                'fao' => 'unit_EstFao',
            ],
            'establishment_address' => [
                'addressLine1' => 'unit_EstAdrLine1',
                'countryCode' => 'unit_EstAdrCountryId',
            ],
            'consultant' => [
                'add-transport-consultant' => 'Y',
                'writtenPermissionToEngage' => 'unit_TcPerm',
                'transportConsultantName' => 'unit_TcFao',
            ],
            'consultantAddress' => [
                'addressLine1' => 'unit_EstAdrLine1',
                'countryCode' => [
                    'id' => 'unit_EstAdrCountryId',
                ],
            ],
            'consultantContact' => [
                'phone_primary' => 'unit_TrPhoneNr1',
                'phone_primary_id' => 'unit_TrPhoneId1',
                'phone_primary_version' => 'unit_TrPhoneVersion',
                'email' => 'unit_TrEmail',
            ],
        ];
    }

    public function testMapFromResult(): void
    {
        $this->assertEquals($this->formData, Addresses::mapFromResult($this->apiData));
    }

    public function testMapFromResultEmptyApiData(): void
    {
        $this->assertEquals([], Addresses::mapFromResult(
            [
                'correspondenceCd' => null,
                'establishmentCd' => null,
                'transportConsultantCd' => null,
            ]
        ));
    }

    public function testMapFromForm(): void
    {
        $this->assertEquals([
            'correspondence' => [
                'id' => 'unit_CdId',
                'version' => 'unit_CdVer',
                'fao' => 'unit_CdFao',
            ],
            'correspondenceAddress' => [
                'addressLine1' => 'unit_CdAdrLine1',
                'countryCode' => 'unit_CdAdrCountryId',
            ],
            'contact' => [
                'phone_primary' => 'unit_CdPhoneNr1',
                'phone_primary_id' => 'unit_CdPhoneId1',
                'phone_primary_version' => 'unit_CdPhoneVersion',
                'email' => 'unit_CdEmail',
            ],
            'establishment' => [
                'id' => 'unit_EstId',
                'version' => 'unit_EstVer',
                'fao' => 'unit_EstFao',
            ],
            'establishmentAddress' => [
                'addressLine1' => 'unit_EstAdrLine1',
                'countryCode' => 'unit_EstAdrCountryId',
            ],
            'consultant' => [
                'add-transport-consultant' => 'Y',
                'writtenPermissionToEngage' => 'unit_TcPerm',
                'transportConsultantName' => 'unit_TcFao',
                'address' => [
                    'addressLine1' => 'unit_EstAdrLine1',
                    'countryCode' => [
                        'id' => 'unit_EstAdrCountryId',
                    ],
                ],
                'contact' => [
                    'phone_primary' => 'unit_TrPhoneNr1',
                    'phone_primary_id' => 'unit_TrPhoneId1',
                    'phone_primary_version' => 'unit_TrPhoneVersion',
                    'email' => 'unit_TrEmail',
                ],
            ],
        ], Addresses::mapFromForm($this->formData));
    }

    public function testSmsContactMapsToPhoneSms(): void
    {
        $this->apiData['correspondenceCd']['phoneContacts'][] = [
            'phoneContactType' => ['id' => 'phone_t_SMS'],
            'phoneNumber' => '+447700900123',
            'id' => 'unit_SmsId',
            'version' => 'unit_SmsVersion',
        ];

        $actual = Addresses::mapFromResult($this->apiData);

        // stored as E.164, shown as people write it
        $this->assertSame('07700 900123', $actual['contact']['phone_sms']);
        $this->assertSame('unit_SmsId', $actual['contact']['phone_sms_id']);
        $this->assertSame('unit_SmsVersion', $actual['contact']['phone_sms_version']);
    }

    public function testDuplicateSmsRowsShowTheMostRecentlyModified(): void
    {
        $sms = static fn (string $id, string $number, ?string $modified, ?string $created) => [
            'phoneContactType' => ['id' => 'phone_t_SMS'],
            'phoneNumber' => $number,
            'id' => $id,
            'version' => '1',
            'lastModifiedOn' => $modified,
            'createdOn' => $created,
        ];
        $this->apiData['correspondenceCd']['phoneContacts'][] = $sms('1', '+447700900001', '2026-03-01T10:00:00+0000', '2026-01-01T10:00:00+0000');
        $this->apiData['correspondenceCd']['phoneContacts'][] = $sms('2', '+447700900002', '2026-05-01T10:00:00+0000', '2026-01-01T10:00:00+0000');
        $this->apiData['correspondenceCd']['phoneContacts'][] = $sms('3', '+447700900003', null, '2026-04-01T10:00:00+0000');

        $actual = Addresses::mapFromResult($this->apiData);

        $this->assertSame('07700 900002', $actual['contact']['phone_sms']);
        $this->assertSame('2', $actual['contact']['phone_sms_id']);
    }

    public function testDuplicateSmsRowsWithNoDatesUseTheHighestId(): void
    {
        foreach (['7', '9', '8'] as $id) {
            $this->apiData['correspondenceCd']['phoneContacts'][] = [
                'phoneContactType' => ['id' => 'phone_t_SMS'],
                'phoneNumber' => '+44770090000' . $id,
                'id' => $id,
                'version' => '1',
            ];
        }

        $actual = Addresses::mapFromResult($this->apiData);

        $this->assertSame('9', $actual['contact']['phone_sms_id']);
    }
}
