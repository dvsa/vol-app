<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\OperatorBusinessDetails as Sut;
use Laminas\Form\Form;

/**
 * Operator Business Details Mapper Test
 */
class OperatorBusinessDetailsTest extends MockeryTestCase
{
    public function testMapFromErrors()
    {
        $mockForm = new Form();

        $errors = [
            'name'          => ['error1'],
            'address'       => ['addressLine1' => ['error2']],
            'companyNumber' => ['error3'],
            'general'       => ['error4'],
        ];

        $expected = ['general' => ['error4']];

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromForm()
    {
        $data = [
            'operator-business-type' => [
                'type' => 'type',
            ],
            'operator-details'       => [
                'companyNumber'    => [
                    'company_number' => '12345678',
                ],
                'name'             => 'name',
                'natureOfBusiness' => 'nob',
                'firstName'        => 'fname',
                'lastName'         => 'lname',
                'personId'         => 1,
                'personVersion'    => 2,
                'id'               => 3,
                'version'          => 4,
                'isIrfo'           => 'Y',
                'allowEmail'       => 'Y',
            ],
            'registeredAddress'      => 'address',
        ];

        $expected = [
            'businessType'     => 'type',
            'companyNumber'    => '12345678',
            'name'             => 'name',
            'natureOfBusiness' => 'nob',
            'firstName'        => 'fname',
            'lastName'         => 'lname',
            'personId'         => 1,
            'personVersion'    => 2,
            'id'               => 3,
            'version'          => 4,
            'address'          => 'address',
            'isIrfo'           => 'Y',
            'cpid'             => null,
            'allowEmail'       => 'Y',
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromResult()
    {
        $data = [
            'id'                  => 1,
            'version'             => 2,
            'name'                => 'name',
            'isIrfo'              => 'Y',
            'companyOrLlpNo'      => '12345678',
            'cpid'                => [
                'id' => 'type',
            ],
            'type'                => [
                'id' => 'type',
            ],
            'contactDetails'      => [
                'address' => 'address',
            ],
            'organisationPersons' => [
                [
                    'person' => [
                        'forename'   => 'fname',
                        'familyName' => 'lname',
                        'id'         => 3,
                        'version'    => 4,
                    ],
                ],
            ],
            'natureOfBusiness'    => 'nob',
            'allowEmail'          => 'Y',
        ];

        $expected = [
            'operator-cpid'          => [
                'type' => 'type',
            ],
            'operator-business-type' => [
                'type' => 'type',
            ],
            'operator-details'       => [
                'id'               => 1,
                'version'          => 2,
                'name'             => 'name',
                'isIrfo'           => 'Y',
                'companyNumber'    => [
                    'company_number' => '12345678',
                ],
                'firstName'        => 'fname',
                'lastName'         => 'lname',
                'personId'         => 3,
                'personVersion'    => 4,
                'natureOfBusiness' => 'nob',
                'allowEmail'       => 'Y',
            ],
            'registeredAddress'      => 'address',
        ];

        $this->assertEquals($expected, Sut::mapFromResult($data));
    }
}
