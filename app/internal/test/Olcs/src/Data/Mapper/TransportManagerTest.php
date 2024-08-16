<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\TransportManager as Sut;
use Laminas\Form\Form;

/**
 * Transport Manager Mapper Test
 */
class TransportManagerTest extends MockeryTestCase
{
    public function testMapFromErrors()
    {
        $mockForm = new Form();

        $errors['messages'] = [
            'homeAddressLine1' => ['error1'],
            'workAddressLine1' => ['error2'],
            'firstName'        => ['error3'],
            'general'          => ['error4'],
        ];

        $expected = ['general' => ['error4']];

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromForm()
    {
        $data = [
            'transport-manager-details' => ['foo' => 'bar'],
            'home-address'              => [
                'addressLine1' => 'line1',
                'addressLine2' => 'line2',
                'addressLine3' => 'line3',
                'addressLine4' => 'line4',
                'town'         => 'town',
                'postcode'     => 'postcode',
                'countryCode'  => 'GB',
                'id'           => 1,
                'version'      => 2,
            ],
            'work-address'              => [
                'addressLine1' => 'line1',
                'addressLine2' => 'line2',
                'addressLine3' => 'line3',
                'addressLine4' => 'line4',
                'town'         => 'town',
                'postcode'     => 'postcode',
                'countryCode'  => 'GB',
                'id'           => 1,
                'version'      => 2,
            ],
        ];

        $expected = [
            'foo'                => 'bar',
            'homeAddressLine1'   => 'line1',
            'homeAddressLine2'   => 'line2',
            'homeAddressLine3'   => 'line3',
            'homeAddressLine4'   => 'line4',
            'homeTown'           => 'town',
            'homePostcode'       => 'postcode',
            'homeCountryCode'    => 'GB',
            'workAddressLine1'   => 'line1',
            'workAddressLine2'   => 'line2',
            'workAddressLine3'   => 'line3',
            'workAddressLine4'   => 'line4',
            'workTown'           => 'town',
            'workPostcode'       => 'postcode',
            'workCountryCode'    => 'GB',
            'homeAddressId'      => 1,
            'homeAddressVersion' => 2,
            'workAddressId'      => 1,
            'workAddressVersion' => 2,
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromResult()
    {
        $data = [
            'homeCd'      => [
                'address'      => [
                    'id'           => 1,
                    'version'      => 2,
                    'addressLine1' => 'hal1',
                    'addressLine2' => 'hal2',
                    'addressLine3' => 'hal3',
                    'addressLine4' => 'hal4',
                    'town'         => 'ht',
                    'postcode'     => 'hpc',
                    'countryCode'  => [
                        'id' => 'hcc',
                    ],
                ],
                'person'       => [
                    'id'         => 3,
                    'version'    => 4,
                    'forename'   => 'forename',
                    'familyName' => 'familyName',
                    'title'      => [
                        'id' => 'title',
                    ],
                    'birthDate'  => '2015-01-01',
                    'birthPlace' => 'bp',
                ],
                'emailAddress' => 'email@address.com',
                'id'           => 5,
                'version'      => 6,
            ],
            'workCd'      => [
                'address' => [
                    'id'           => 7,
                    'version'      => 8,
                    'addressLine1' => 'wal1',
                    'addressLine2' => 'wal2',
                    'addressLine3' => 'wal3',
                    'addressLine4' => 'wal4',
                    'town'         => 'wt',
                    'postcode'     => 'wpc',
                    'countryCode'  => [
                        'id' => 'wcc',
                    ],
                ],
            ],
            'id'          => 11,
            'version'     => 12,
            'tmType'      => [
                'id' => 'type',
            ],
            'tmStatus'    => [
                'id' => 'status',
            ],
            'removedDate' => 'REMOVED_DATE',
        ];
        $expected = [
            'transport-manager-details' => [
                'id'            => 11,
                'version'       => 12,
                'homeCdId'      => 5,
                'homeCdVersion' => 6,
                'personId'      => 3,
                'personVersion' => 4,
                'type'          => 'type',
                'status'        => 'status',
                'firstName'     => 'forename',
                'lastName'      => 'familyName',
                'birthDate'     => '2015-01-01',
                'birthPlace'    => 'bp',
                'title'         => 'title',
                'emailAddress'  => 'email@address.com',
                'removedDate'   => 'REMOVED_DATE',
            ],
            'home-address'              => [
                'id'           => 1,
                'version'      => 2,
                'addressLine1' => 'hal1',
                'addressLine2' => 'hal2',
                'addressLine3' => 'hal3',
                'addressLine4' => 'hal4',
                'town'         => 'ht',
                'postcode'     => 'hpc',
                'countryCode'  => 'hcc',
            ],
            'work-address'              => [
                'id'           => 7,
                'version'      => 8,
                'addressLine1' => 'wal1',
                'addressLine2' => 'wal2',
                'addressLine3' => 'wal3',
                'addressLine4' => 'wal4',
                'town'         => 'wt',
                'postcode'     => 'wpc',
                'countryCode'  => 'wcc',
            ],
        ];

        $this->assertEquals($expected, Sut::mapFromResult($data));
    }
}
