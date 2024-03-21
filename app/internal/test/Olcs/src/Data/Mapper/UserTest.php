<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\User as Sut;
use Laminas\Form\Form;

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
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'createdOn' => '2012-06-01 17:11:12',
                    'lastLoggedInOn' => '2016-12-06T16:12:46+0000',
                    'lockedOn' => '2016-10-01T10:11:46+0000',
                    'latestPasswordResetEvent' => [
                        'eventData' => 'By email',
                        'eventDatetime' => '2016-12-09 14:13:36',
                    ],
                    'accountDisabled' => 'Y',
                    'disabledDate' => '2015-06-07 17:11:12',
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
                                'phoneContactType' => ['id' => 'phone_t_primary'],
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => ['id' => 'phone_t_secondary'],
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                    'translateToWelsh' => 'Y',
                    'osType' => 'windows_10'
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'createdOn' => '2012-06-01 17:11:12',
                        'lastLoggedInOn' => '2016-12-06T16:12:46+0000',
                        'passwordLastReset' => 'By email on 09/12/2016 14:13:36',
                        'accountDisabled' => 'Y',
                        'disabledDate' => '2015-06-07 17:11:12',
                        'locked' => 'Yes on 01/10/2016 10:11:46',
                    ],
                    'userType' => [
                        'id' => 987,
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
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 304,
                        'phone_secondary_version' => 4,
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1'
                    ],
                    'userSettings' => [
                        'translateToWelsh' => 'Y',
                        'osType' => 'windows_10'
                    ],
                ]
            ],
            // edit - transport-manager
            [
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'createdOn' => '2012-06-01 17:11:12',
                    'lastLoggedInOn' => null,
                    'accountDisabled' => 'Y',
                    'userType' => 'transport-manager',
                    'roles' => [
                        [
                            'id' => 99,
                            'role' => 'role',
                        ]
                    ],
                    'transportManager' => [
                        'id' => 3,
                        'homeCd' => [
                            'person' => [
                                'forename' => 'test',
                                'familyName' => 'me'
                            ]
                        ]
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
                    'translateToWelsh' => 'N',
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'createdOn' => '2012-06-01 17:11:12',
                        'lastLoggedInOn' => null,
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                        'locked' => 'No',
                    ],
                    'userType' => [
                        'id' => 987,
                        'userType' => 'transport-manager',
                        'role' => 'role',
                        'currentTransportManager' => 3,
                        'currentTransportManagerName' => 'test me',
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
                    'userSettings' => [
                        'translateToWelsh' => 'N',
                    ],
                ]
            ],
            // edit - partner
            [
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'createdOn' => '2012-06-01 17:11:12',
                    'lastLoggedInOn' => null,
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
                    'translateToWelsh' => 'N',
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'createdOn' => '2012-06-01 17:11:12',
                        'lastLoggedInOn' => null,
                        'accountDisabled' => 'Y',
                        'locked' => 'No',
                    ],
                    'userType' => [
                        'id' => 987,
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
                    'userSettings' => [
                        'translateToWelsh' => 'N',
                    ],
                ]
            ],
            // edit - local-authority
            [
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'createdOn' => '2012-06-01 17:11:12',
                    'lastLoggedInOn' => null,
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
                    'translateToWelsh' => 'N',
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'createdOn' => '2012-06-01 17:11:12',
                        'lastLoggedInOn' => null,
                        'accountDisabled' => 'Y',
                        'locked' => 'No',
                    ],
                    'userType' => [
                        'id' => 987,
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
                    'userSettings' => [
                        'translateToWelsh' => 'N',
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
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                        'resetPassword' => 'Y',
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
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 304,
                        'phone_secondary_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                    'userSettings' => [
                        'translateToWelsh' => 'Y',
                        'osType' => 'windows_10'
                    ],
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'resetPassword' => 'Y',
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
                                'phoneContactType' => 'phone_t_primary',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_secondary',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                    'translateToWelsh' => 'Y',
                    'osType' => 'windows_10'
                ],
            ],
            // edit - transport-manager
            [
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                        'resetPassword' => 'N',
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
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 304,
                        'phone_secondary_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                    'userSettings' => [
                        'translateToWelsh' => 'N',
                    ],
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'resetPassword' => 'N',
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
                                'phoneContactType' => 'phone_t_primary',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_secondary',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                    'translateToWelsh' => 'N',
                ],
            ],
            // edit - partner
            [
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                        'resetPassword' => 'N',
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
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 304,
                        'phone_secondary_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                    'userSettings' => [
                        'translateToWelsh' => 'N',
                    ],
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'resetPassword' => 'N',
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
                                'phoneContactType' => 'phone_t_primary',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_secondary',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                    'translateToWelsh' => 'N',
                ],
            ],
            // edit - local-authority
            [
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                        'resetPassword' => 'N',
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
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 304,
                        'phone_secondary_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                    'userSettings' => [
                        'translateToWelsh' => 'N',
                    ],
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'resetPassword' => 'N',
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
                                'phoneContactType' => 'phone_t_primary',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_secondary',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                    'translateToWelsh' => 'N',
                ],
            ],
            // edit - operator
            [
                "inData" => [
                    'id' => 987,
                    'version' => 1,
                    'userLoginSecurity' => [
                        'loginId' => 'testuser',
                        'accountDisabled' => 'Y',
                        'resetPassword' => 'N',
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
                        'phone_primary' => 'pn1',
                        'phone_primary_id' => 301,
                        'phone_primary_version' => 1,
                        'phone_secondary' => 'pn2',
                        'phone_secondary_id' => 304,
                        'phone_secondary_version' => 4,
                    ],
                    'address' => [
                        'addressLine1' => 'a1'
                    ],
                    'userSettings' => [
                        'translateToWelsh' => 'N',
                    ],
                ],
                "expected" => [
                    'id' => 987,
                    'version' => 1,
                    'loginId' => 'testuser',
                    'accountDisabled' => 'Y',
                    'resetPassword' => 'N',
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
                                'phoneContactType' => 'phone_t_primary',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_secondary',
                                'phoneNumber' => 'pn2',
                            ],
                        ],
                    ],
                    'translateToWelsh' => 'N',
                ],
            ],
        ];
    }

    /**
    * @dataProvider dpMapFromErrors
    */
    public function testMapFromErrors($errors, $expectedFormErrors, $expected)
    {
        $mockForm = m::mock(Form::class)
            ->shouldReceive('setMessages')
            ->with($expectedFormErrors)
            ->times($expectedFormErrors ? 1 : 0)
            ->getMock();

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }

    public function dpMapFromErrors()
    {
        return [
            'username error' => [
                'errors' => [
                    'messages' => [
                        'loginId' => ['err'],
                        'general' => 'error'
                    ]
                ],
                'expectedFormErrors' => [
                    'userLoginSecurity' => ['loginId' => ['err']]
                ],
                'expected' => [
                    'messages' => [
                        'general' => 'error'
                    ]
                ],
            ],
            'role error' => [
                'errors' => [
                    'messages' => [
                        'role' => ['err'],
                        'general' => 'error'
                    ]
                ],
                'expectedFormErrors' => [
                    'userType' => ['role' => ['err']]
                ],
                'expected' => [
                    'messages' => [
                        'general' => 'error'
                    ]
                ],
            ],
            'general error' => [
                'errors' => [
                    'messages' => [
                        'general' => 'error'
                    ]
                ],
                'expectedFormErrors' => null,
                'expected' => [
                    'messages' => [
                        'general' => 'error'
                    ]
                ],
            ],
        ];
    }
}
