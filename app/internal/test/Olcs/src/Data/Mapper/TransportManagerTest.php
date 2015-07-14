<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\TransportManager as Sut;
use Zend\Form\Form;

/**
 * Transport Manager Mapper Test
 */
class TransportManagerTest extends MockeryTestCase
{
    public function testMapFromErrors()
    {
        $mockForm = m::mock(Form::class)->makePartial();
        $errors = [
            'homeAddressLine1' => ['error1'],
            'workAddressLine1' => ['error2'],
            'firstName' => ['error3'],
            'general' => ['error4'],
        ];
        $expected = [
            'general' => ['error4']
        ];
        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromForm()
    {
        $data = [
            'transport-manager-details' => ['foo' => 'bar'],
            'home-address' => [
                'addressLine1' => 'line1',
                'addressLine2' => 'line2',
                'addressLine3' => 'line3',
                'addressLine4' => 'line4',
                'town'         => 'town',
                'postcode'     => 'postcode',
                'countryCode'  => 'GB',
            ],
            'work-address' => [
                'addressLine1' => 'line1',
                'addressLine2' => 'line2',
                'addressLine3' => 'line3',
                'addressLine4' => 'line4',
                'town'         => 'town',
                'postcode'     => 'postcode',
                'countryCode'  => 'GB',
            ],
        ];

        $expected = [
            'foo' => 'bar',
            'homeAddressLine1' => 'line1',
            'homeAddressLine2' => 'line2',
            'homeAddressLine3' => 'line3',
            'homeAddressLine4' => 'line4',
            'homeTown'         => 'town',
            'homePostcode'     => 'postcode',
            'homeCountryCode'  => 'GB',
            'workAddressLine1' => 'line1',
            'workAddressLine2' => 'line2',
            'workAddressLine3' => 'line3',
            'workAddressLine4' => 'line4',
            'workTown'         => 'town',
            'workPostcode'     => 'postcode',
            'workCountryCode'  => 'GB'
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }
}
