<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\User as Sut;
use Zend\Form\Form;

/**
 * User Mapper Test
 */
class UserTest extends MockeryTestCase
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
                []
            ],
            // edit - internal
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'lockedDate' => '2015-06-07 17:11:12',
                    'userType' => 'internal',
                    'roles' => [
                        [
                            'id' => 99,
                            'role' => 'role',
                        ]
                    ],
                    'team' => [
                        'id' => 3
                    ],
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'id' => 200,
                            'version' => 1,
                            'addressLine1' => 'a1'
                        ],
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => ['id' => 'phone_t_tel'],
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => ['id' => 'phone_t_fax'],
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                        'lockedDate' => '07/06/2015 17:11:12',
                    ],
                    'userType' => [
                        'userType' => 'internal',
                        'role' => 'role',
                        'team' => 3,
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
                        'phone_fax' => 'pn2',
                        'phone_fax_id' => 304,
                        'phone_fax_version' => 4,
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1'
                    ],
                ]
            ],
            // edit - transport-manager
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'userType' => 'transport-manager',
                    'roles' => [
                        [
                            'id' => 99,
                            'role' => 'role',
                        ]
                    ],
                    'transportManager' => [
                        'id' => 3
                    ],
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'id' => 200,
                            'version' => 1,
                            'addressLine1' => 'a1'
                        ],
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                    ],
                    'userType' => [
                        'userType' => 'transport-manager',
                        'role' => 'role',
                        'transportManager' => 3,
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1'
                    ],
                ]
            ],
            // edit - partner
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'userType' => 'partner',
                    'roles' => [
                        [
                            'id' => 99,
                            'role' => 'role',
                        ]
                    ],
                    'partnerContactDetails' => [
                        'id' => 3
                    ],
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'id' => 200,
                            'version' => 1,
                            'addressLine1' => 'a1'
                        ],
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                    ],
                    'userType' => [
                        'userType' => 'partner',
                        'role' => 'role',
                        'partnerContactDetails' => 3,
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1'
                    ],
                ]
            ],
            // edit - local-authority
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'userType' => 'local-authority',
                    'roles' => [
                        [
                            'id' => 99,
                            'role' => 'role',
                        ]
                    ],
                    'localAuthority' => [
                        'id' => 3
                    ],
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'id' => 200,
                            'version' => 1,
                            'addressLine1' => 'a1'
                        ],
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                    ],
                    'userType' => [
                        'userType' => 'local-authority',
                        'role' => 'role',
                        'localAuthority' => 3,
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1'
                    ],
                ]
            ],
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
            // edit - internal
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                    ],
                    'userType' => [
                        'userType' => 'internal',
                        'role' => 'role',
                        'team' => 3,
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
                        'phone_fax' => 'pn2',
                        'phone_fax_id' => 304,
                        'phone_fax_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'userType' => 'internal',
                    'roles' => ['role'],
                    'team' => 3,
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'addressLine1' => 'a1'
                        ],
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => 'phone_t_tel',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_fax',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                ],
            ],
            // edit - transport-manager
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                    ],
                    'userType' => [
                        'userType' => 'transport-manager',
                        'role' => 'role',
                        'applicationTransportManagers' => ['application' => 97],
                        'transportManager' => 3,
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
                        'phone_fax' => 'pn2',
                        'phone_fax_id' => 304,
                        'phone_fax_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'userType' => 'transport-manager',
                    'roles' => ['role'],
                    'application' => 97,
                    'transportManager' => 3,
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'addressLine1' => 'a1'
                        ],
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => 'phone_t_tel',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_fax',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                ],
            ],
            // edit - partner
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                    ],
                    'userType' => [
                        'userType' => 'partner',
                        'role' => 'role',
                        'partnerContactDetails' => 3,
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
                        'phone_fax' => 'pn2',
                        'phone_fax_id' => 304,
                        'phone_fax_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'userType' => 'partner',
                    'roles' => ['role'],
                    'partnerContactDetails' => 3,
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'addressLine1' => 'a1'
                        ],
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => 'phone_t_tel',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_fax',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                ],
            ],
            // edit - local-authority
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                    ],
                    'userType' => [
                        'userType' => 'local-authority',
                        'role' => 'role',
                        'localAuthority' => 3,
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
                        'phone_fax' => 'pn2',
                        'phone_fax_id' => 304,
                        'phone_fax_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'userType' => 'local-authority',
                    'roles' => ['role'],
                    'localAuthority' => 3,
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'addressLine1' => 'a1'
                        ],
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => 'phone_t_tel',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_fax',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                ],
            ],
            // edit - operator
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                    ],
                    'userType' => [
                        'userType' => 'operator',
                        'role' => 'role',
                        'licenceNumber' => 'licNo',
                    ],
                    'userPersonal' => [
                        'forename' => 'fn1',
                        'familyName' => 'ln1',
                        'birthDate' => '2012-03-01',
                    ],
                    'userContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'emailConfirm' => 'test@test.me',
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
                        'phone_fax' => 'pn2',
                        'phone_fax_id' => 304,
                        'phone_fax_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'userType' => 'operator',
                    'roles' => ['role'],
                    'licenceNumber' => 'licNo',
                    'contactDetails' => [
                        'person' => [
                            'forename' => 'fn1',
                            'familyName' => 'ln1',
                            'birthDate' => '2012-03-01',
                        ],
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'addressLine1' => 'a1'
                        ],
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => 'phone_t_tel',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_fax',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }


    public function testMapFromErrors()
    {
        $errors = [
            'messages' => [
                'loginId' => ['err'],
                'general' => 'error'
            ]
        ];
        $expected = [
            'messages' => [
                'general' => 'error'
            ]
        ];
        $mockForm = m::mock(Form::class)
            ->shouldReceive('setMessages')
            ->with(['userLoginSecurity' => ['loginId' => ['err']]])
            ->once()
            ->getMock();

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }
}
