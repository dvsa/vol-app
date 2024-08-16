<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Opposition as Sut;
use Laminas\Form\FormInterface;

/**
 * Opposition Mapper Test
 */
class OppositionTest extends MockeryTestCase
{
    /**
    * @dataProvider mapFromResultDataProvider
    *
    * @param $inData
    * @param $expected
    */
    public function testMapFromResult($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public function mapFromResultDataProvider()
    {
        return [
            // add
            [
                [],
                ['fields' => []]
            ],
            // edit
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'opposer' => [
                        'id' => 111,
                        'opposerType' => 'otf_obj',
                        'contactDetails' => [
                            'id' => 555,
                            'description' => 'desc',
                            'address' => [
                                'id' => 200,
                                'version' => 1,
                                'addressLine1' => 'a1'
                            ],
                            'emailAddress' => 'test@test.me',
                            'phoneContacts' => [
                                [
                                    'id' => 301,
                                    'version' => 1,
                                    'phoneContactType' => ['id' => 'phone_t_primary'],
                                    'phoneNumber' => 'pn1',
                                ],
                                [
                                    'id' => 302,
                                    'version' => 4,
                                    'phoneContactType' => ['id' => 'phone_t_secondary'],
                                    'phoneNumber' => 'pn2',
                                ],
                            ],
                            'person' => [
                                'id' => 400,
                                'version' => 1,
                                'forename' => 'forename'
                            ],
                        ]
                    ],
                    'operatingCentres' => [800, 801],
                    'case' => [
                        'id' => 120,
                        'application' => [
                            'id' => 123
                        ]
                    ]
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'version' => 1,
                        'opposer' => 111,
                        'opposerType' => 'otf_obj',
                        'contactDetailsDescription' => 'desc',
                        'case' => 120,
                        'applicationOperatingCentres' => [800, 801],
                    ],
                    'contact' => [
                        'emailAddress' => 'test@test.me',
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 302,
                        'phone_secondary_version' => 4,
                    ],
                    'person' => [
                        'id' => 400,
                        'version' => 1,
                        'forename' => 'forename'
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1',
                    ],
                ]
            ]
        ];
    }

    /**
    * @dataProvider mapFromFormDataProvider
    *
    * @param $inData
    * @param $expected
    */
    public function testMapFromForm($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public function mapFromFormDataProvider()
    {
        return [
            'Application operating centre' => [
                [
                    'fields' => [
                        'id' => 987,
                        'version' => 1,
                        'opposerType' => 'otf_obj',
                        'contactDetailsDescription' => 'desc',
                        'applicationOperatingCentres' => [800, 801],
                    ],
                    'contact' => [
                        'emailAddress' => 'test@test.me',
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 303,
                        'phone_secondary_version' => 3,
                    ],
                    'person' => [
                        'forename' => 'forename'
                    ],
                    'address' => [
                        'addressLine1' => 'a1',
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'opposerType' => 'otf_obj',
                    'contactDetailsDescription' => 'desc',
                    'applicationOperatingCentres' => [800, 801],
                    'opposerContactDetails' => [
                        'description' => 'desc',
                        'address' => [
                            'addressLine1' => 'a1'
                        ],
                        'emailAddress' => 'test@test.me',
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => 'phone_t_primary',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 303,
                                'version' => 3,
                                'phoneContactType' => 'phone_t_secondary',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                        'person' => [
                            'forename' => 'forename'
                        ],
                    ],
                    'operatingCentres' => [800, 801],
                ]
            ],
            'Licensing operating centre' => [
                [
                    'fields' => [
                        'id' => 987,
                        'version' => 1,
                        'opposerType' => 'otf_obj',
                        'contactDetailsDescription' => 'desc',
                        'licenceOperatingCentres' => [800, 801],
                    ],
                    'contact' => [
                        'emailAddress' => 'test@test.me',
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 303,
                        'phone_secondary_version' => 3,
                    ],
                    'person' => [
                        'forename' => 'forename'
                    ],
                    'address' => [
                        'addressLine1' => 'a1',
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'opposerType' => 'otf_obj',
                    'contactDetailsDescription' => 'desc',
                    'licenceOperatingCentres' => [800, 801],
                    'opposerContactDetails' => [
                        'description' => 'desc',
                        'address' => [
                            'addressLine1' => 'a1'
                        ],
                        'emailAddress' => 'test@test.me',
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => 'phone_t_primary',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 303,
                                'version' => 3,
                                'phoneContactType' => 'phone_t_secondary',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                        'person' => [
                            'forename' => 'forename'
                        ],
                    ],
                    'operatingCentres' => [800, 801],
                ]
            ],
        ];
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
