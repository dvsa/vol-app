<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Opposition as Sut;
use Zend\Form\FormInterface;

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
                                    'phoneContactType' => ['id' => 'phone_t_tel'],
                                    'phoneNumber' => 'pn1',
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
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
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
            [
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
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
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
                                'phoneContactType' => 'phone_t_tel',
                                'phoneNumber' => 'pn1',
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
