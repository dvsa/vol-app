<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\OperatorBusinessDetails as Sut;
use Zend\Form\Form;

/**
 * Operator Business Details Mapper Test
 */
class OperatorBusinessDetailsTest extends MockeryTestCase
{
    public function testMapFromErrors()
    {
        $mockForm = m::mock(Form::class)->makePartial();
        $errors = [
            'name' => ['error1'],
            'addressLine1' => ['error2'],
            'companyNumber' => ['error3'],
            'general' => ['error4']
        ];
        $expected = [
            'general' => ['error4']
        ];
        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromForm()
    {
        $data = [
            'operator-business-type' => [
                'type' => 'type'
            ],
            'operator-details' => [
                'companyNumber' => [
                    'company_number' => '12345678'
                ],
                'name' => 'name',
                'natureOfBusinesses' => 'nob',
                'firstName' => 'fname',
                'lastName' => 'lname',
                'personId' => 1,
                'personVersion' => 2,
                'id' => 3,
                'version' => 4,
                'isIrfo' => 'Y'
            ],
            'registeredAddress' => 'address'
        ];

        $expected = [
            'businessType' => 'type',
            'companyNumber' => '12345678',
            'name' => 'name',
            'natureOfBusiness' => 'nob',
            'firstName' => 'fname',
            'lastName' => 'lname',
            'personId' => 1,
            'personVersion' => 2,
            'id' => 3,
            'version' => 4,
            'address' => 'address',
            'isIrfo' => 'Y'
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromResult()
    {
        $data = [
            'id' => 1,
            'version' => 2,
            'name' => 'name',
            'isIrfo' => 'Y',
            'companyOrLlpNo' => '12345678',
            'type' => [
                'id' => 'type'
            ],
            'contactDetails' => [
                'address' => 'address'
            ],
            'organisationPersons' => [
                [
                    'person' => [
                        'forename' => 'fname',
                        'familyName' => 'lname',
                        'id' => 3,
                        'version' => 4
                    ]
                ]
            ],
            'natureOfBusinesses' => [
                [
                    'id' => 5
                ]
            ]
        ];

        $expected = [
            'operator-business-type' => [
                'type' => 'type'
            ],
            'operator-details' => [
                'id' => 1,
                'version' => 2,
                'name' => 'name',
                'isIrfo' => 'Y',
                'companyNumber' => [
                    'company_number' => '12345678'
                ],
                'firstName' => 'fname',
                'lastName' => 'lname',
                'personId' => 3,
                'personVersion' => 4,
                'natureOfBusinesses' => [5]
            ],
            'registeredAddress' => 'address'
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }
}
