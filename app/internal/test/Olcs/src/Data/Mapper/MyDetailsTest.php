<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\MyDetails as Sut;
use Laminas\Form\Form;

/**
 * MyDetails Mapper Test
 */
class MyDetailsTest extends MockeryTestCase
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
            // edit
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'team' => [
                        'id' => 111,
                    ],
                    'loginId' => 'login id',
                    'contactDetails' => [
                        'id' => 555,
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
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => ['id' => 'phone_t_secondary'],
                                'phoneNumber' => 'pn4',
                            ],
                        ],
                        'person' => [
                            'id' => 400,
                            'version' => 1,
                            'forename' => 'forename'
                        ],
                    ],
                    'translateToWelsh' => 'Y',
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'userDetails' => [
                        'team' => 111,
                    ],
                    'officeAddress' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1',
                    ],
                    'userContact' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn4',
                        'phone_secondary_id' => 304,
                        'phone_secondary_version' => 4,
                    ],
                    'person' => [
                        'id' => 400,
                        'version' => 1,
                        'forename' => 'forename'
                    ],
                    'userSettings' => [
                        'translateToWelsh' => 'Y',
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
                    'id' => 987,
                    'version' => 1,
                    'userDetails' => [
                        'team' => 111,
                        'loginId' => 'login id',
                    ],
                    'officeAddress' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1',
                    ],
                    'userContact' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn4',
                        'phone_secondary_id' => 304,
                        'phone_secondary_version' => 4,
                    ],
                    'person' => [
                        'id' => 400,
                        'version' => 1,
                        'forename' => 'forename'
                    ],
                    'userSettings' => [
                        'translateToWelsh' => 'N',
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'team' => 111,
                    'contactDetails' => [
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
                                'phoneContactType' => 'phone_t_primary',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_secondary',
                                'phoneNumber' => 'pn4',
                            ],
                        ],
                        'person' => [
                            'id' => 400,
                            'version' => 1,
                            'forename' => 'forename'
                        ],
                    ],
                    'translateToWelsh' => 'N',
                ]
            ],
        ];
    }

    public function testMapFromErrors()
    {
        $errors = [
            'messages' => [
                'general' => 'error'
            ]
        ];
        $expected = [
            'messages' => [
                'general' => 'error'
            ]
        ];
        $mockForm = m::mock(Form::class);

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }
}
