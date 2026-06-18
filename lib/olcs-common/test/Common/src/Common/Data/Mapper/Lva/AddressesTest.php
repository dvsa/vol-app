<?php

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\Addresses;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Data\Mapper\Lva\Addresses
 */
class AddressesTest extends MockeryTestCase
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
        static::assertEquals(
            $this->formData,
            Addresses::mapFromResult($this->apiData)
        );
    }

    public function testMapFromResultEmptyApiData(): void
    {
        static::assertEquals(
            [],
            Addresses::mapFromResult(
                [
                    'correspondenceCd' => null,
                    'establishmentCd' => null,
                    'transportConsultantCd' => null,
                ]
            )
        );
    }

    public function testMapFromForm(): void
    {
        static::assertEquals(
            [
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
            ],
            Addresses::mapFromForm($this->formData)
        );
    }
}
