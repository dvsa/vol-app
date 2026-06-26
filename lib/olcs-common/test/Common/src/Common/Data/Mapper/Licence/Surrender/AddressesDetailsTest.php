<?php

namespace CommonTest\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\AddressDetails;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AddressesDetailsTest extends MockeryTestCase
{
    /** @var  array */
    private $apiData;

    /** @var  array */
    private $formData;

    #[\Override]
    protected function setUp(): void
    {
        $this->apiData = [

            'cnsDate' => '',
            'correspondenceCd' =>
                [
                    'address' =>
                        [
                            'addressLine1' => 'Solway Business Centre',
                            'addressLine2' => 'Kingstown',
                            'addressLine3' => 'Address Line 3',
                            'addressLine4' => 'Address Line 4',
                            'adminArea' => 'area1',
                            'countryCode' =>
                                [
                                    'id' => 'GB',
                                ],
                            'createdBy' => 'me',
                            'createdOn' => '2018-11-02T15:54:17+0000',
                            'deletedDate' => 'today',
                            'id' => '1041',
                            'lastModifiedBy' => 'user1',
                            'lastModifiedOn' => '2019-11-02T15:54:17+0000',
                            'olbsKey' => '8',
                            'olbsType' => 'type1',
                            'paonEnd' => '2019-11-02T17',
                            'paonStart' => '2019-11-02T19',
                            'postcode' => 'CA6 4BY',
                            'town' => 'Carlisle',
                            'uprn' => '8',
                            'version' => '1',
                            'contactDetails' => 'cd',
                        ],
                    'contactType' => '',
                    'createdBy' => '',
                    'createdOn' => '2014 - 11 - 24T10:30:04 + 0000',
                    'deletedDate' => '',
                    'description' => '',
                    'emailAddress' => 'test@test.com',
                    'fao' => 'Sir',
                    'id' => '102',
                    'lastModifiedBy' => '',
                    'lastModifiedOn' => '2018 - 11 - 15T15:47:14 + 0000',
                    'olbsKey' => '',
                    'olbsType' => '',
                    'person' => '',
                    'version' => '3',
                    'writtenPermissionToEngage' => 'N',
                    'phoneContacts' =>
                        [
                            '0' =>
                                [
                                    'contactDetails' => '',
                                    'createdBy' => '',
                                    'createdOn' => '2018 - 11 - 15T16:08:25 + 0000',
                                    'details' => '',
                                    'id' => '1393687',
                                    'lastModifiedBy' => '',
                                    'lastModifiedOn' => '2018 - 11 - 15T16:08:25 + 0000',
                                    'olbsKey' => '8',
                                    'olbsType' => 'type1',
                                    'phoneContactType' =>
                                        [
                                            'description' => 'Secondary',
                                            'displayOrder' => '2',
                                            'id' => 'phone_t_secondary',
                                            'olbsKey' => 'Secondary',
                                            'parent' => '',
                                            'refDataCategoryId' => 'phone_contact_type',
                                            'version' => '1',
                                        ],

                                    'phoneNumber' => '03333333333',
                                    'version' => '1',
                                ],

                            '1' =>
                                [
                                    'contactDetails' => '',
                                    'createdBy' => '',
                                    'createdOn' => '2018 - 11 - 13T14:03:12 + 0000',
                                    'details' => '',
                                    'id' => '1393686',
                                    'lastModifiedBy' => '',
                                    'lastModifiedOn' => '2018 - 11 - 14T15:46:34 + 0000',
                                    'olbsKey' => '',
                                    'olbsType' => '',
                                    'phoneContactType' =>
                                        [
                                            'description' => 'Primary',
                                            'displayOrder' => '1',
                                            'id' => 'phone_t_primary',
                                            'olbsKey' => 'Primary',
                                            'parent' => '',
                                            'refDataCategoryId' => 'phone_contact_type',
                                            'version' => '1',
                                        ],

                                    'phoneNumber' => '04798458588',
                                    'version' => '4',
                                ]
                        ]
                ]
        ];

        $this->formData = [
            'correspondence' => [
                'id' => '102',
                'version' => '3',
                'fao' => 'Sir',
            ],
            'contact' => [
                'phone_primary' => '04798458588',
                'phone_primary_id' => '1393686',
                'phone_primary_version' => '4',
                'phone_secondary' => '03333333333',
                'phone_secondary_id' => '1393687',
                'phone_secondary_version' => '1',
                'email' => 'test@test.com',
            ],
            'correspondence_address' => [
                'addressLine1' => 'Solway Business Centre',
                'addressLine2' => 'Kingstown',
                'addressLine3' => 'Address Line 3',
                'addressLine4' => 'Address Line 4',
                'town' => 'Carlisle',
                'countryCode' => [
                    'id' => 'GB'
                ],
                'adminArea' => 'area1',
                'createdBy' => 'me',
                'createdOn' => '2018-11-02T15:54:17+0000',
                'deletedDate' => 'today',
                'id' => '1041',
                'lastModifiedBy' => 'user1',
                'lastModifiedOn' => '2019-11-02T15:54:17+0000',
                'olbsKey' => '8',
                'olbsType' => 'type1',
                'paonEnd' => '2019-11-02T17',
                'paonStart' => '2019-11-02T19',
                'postcode' => 'CA6 4BY',
                'uprn' => '8',
                'version' => '1',
                'contactDetails' => 'cd',
            ],
        ];
    }

    public function testMapFromResult(): void
    {
        static::assertEquals(
            $this->formData,
            AddressDetails::mapFromResult($this->apiData)
        );
    }

    public function testMapFromResultEmptyApiData(): void
    {
        static::assertEquals(
            [],
            AddressDetails::mapFromResult(
                [
                    'correspondenceCd' => null,
                ]
            )
        );
    }
}
