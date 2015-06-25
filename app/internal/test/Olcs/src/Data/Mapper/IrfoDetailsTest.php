<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\IrfoDetails as Sut;
use Zend\Form\FormInterface;

/**
 * IrfoDetails Mapper Test
 */
class IrfoDetailsTest extends MockeryTestCase
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
                    'irfoContactDetails' => [
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
                                'phoneContactType' => ['id' => 'phone_t_tel'],
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 302,
                                'version' => 2,
                                'phoneContactType' => ['id' => 'phone_t_home'],
                                'phoneNumber' => 'pn2',
                            ],
                            [
                                'id' => 303,
                                'version' => 3,
                                'phoneContactType' => ['id' => 'phone_t_mobile'],
                                'phoneNumber' => 'pn3',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => ['id' => 'phone_t_fax'],
                                'phoneNumber' => 'pn4',
                            ],
                        ],
                    ]
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'irfoContactDetails' => 555,
                        'idHtml' => 987,
                        'version' => 1,
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1',
                    ],
                    'contact' => [
                        'email' => 'test@test.me',
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
                        'phone_home' => 'pn2',
                        'phone_home_id' => 302,
                        'phone_home_version' => 2,
                        'phone_mobile' => 'pn3',
                        'phone_mobile_id' => 303,
                        'phone_mobile_version' => 3,
                        'phone_fax' => 'pn4',
                        'phone_fax_id' => 304,
                        'phone_fax_version' => 4,
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
                        'irfoPartners' => [
                            [
                                'id' => 101,
                                'version' => 1,
                                'name' => 'p1'
                            ],
                            [
                                'name' => 'p2'
                            ],
                            [
                                'name' => ''
                            ]
                        ],
                        'tradingNames' => [
                            [
                                'id' => 201,
                                'version' => 1,
                                'name' => 'tn1'
                            ],
                            [
                                'name' => 'tn2'
                            ],
                            [
                                'name' => ''
                            ]
                        ],
                    ],
                    'address' => [
                        'id' => 200,
                        'version' => 1,
                        'addressLine1' => 'a1',
                    ],
                    'contact' => [
                        'email' => 'test@test.me',
                        'phone_business' => 'pn1',
                        'phone_business_id' => 301,
                        'phone_business_version' => 1,
                        'phone_home' => 'pn2',
                        'phone_home_id' => 302,
                        'phone_home_version' => 2,
                        'phone_mobile' => 'pn3',
                        'phone_mobile_id' => 303,
                        'phone_mobile_version' => 3,
                        'phone_fax' => 'pn4',
                        'phone_fax_id' => 304,
                        'phone_fax_version' => 4,
                    ],
                ],
                [
                    'irfoPartners' => [
                        [
                            'id' => 101,
                            'version' => 1,
                            'name' => 'p1'
                        ],
                        [
                            'name' => 'p2'
                        ],
                    ],
                    'tradingNames' => [
                        [
                            'id' => 201,
                            'version' => 1,
                            'name' => 'tn1'
                        ],
                        [
                            'name' => 'tn2'
                        ],
                    ],
                    'irfoContactDetails' => [
                        'emailAddress' => 'test@test.me',
                        'address' => [
                            'id' => 200,
                            'version' => 1,
                            'addressLine1' => 'a1',
                        ],
                        'phoneContacts' => [
                            [
                                'id' => 301,
                                'version' => 1,
                                'phoneContactType' => 'phone_t_tel',
                                'phoneNumber' => 'pn1',
                            ],
                            [
                                'id' => 302,
                                'version' => 2,
                                'phoneContactType' => 'phone_t_home',
                                'phoneNumber' => 'pn2',
                            ],
                            [
                                'id' => 303,
                                'version' => 3,
                                'phoneContactType' => 'phone_t_mobile',
                                'phoneNumber' => 'pn3',
                            ],
                            [
                                'id' => 304,
                                'version' => 4,
                                'phoneContactType' => 'phone_t_fax',
                                'phoneNumber' => 'pn4',
                            ],
                        ],
                    ]
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
