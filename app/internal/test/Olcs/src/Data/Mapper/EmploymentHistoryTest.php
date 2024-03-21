<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\TransportManager\EmploymentHistory as Sut;
use Laminas\Form\FormInterface;

/**
 * EmploymentHistoryTest Mapper Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class EmploymentHistoryTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $data = [
            'transportManager' => 'TM',
            'employerName' => 'EN',
            'position' => 'P',
            'hoursPerWeek' => 'HPW',
            'id' => 'ID',
            'version' => 'V',
            'contactDetails' => [
                'address' => 'ADD',
            ]
        ];
        $expected = [
            'transportManager' => 'TM',
            'tm-employer-name-details' => [
                'employerName' => 'EN',
            ],
            'tm-employment-details' => [
                'position' => 'P',
                'hoursPerWeek' => 'HPW',
                'id' => 'ID',
                'version' => 'V',
            ],
            'address' => 'ADD',
        ];

        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromForm()
    {
        $data = [
            'transportManager' => 'TM',
            'tm-employer-name-details' => [
                'employerName' => 'EN',
            ],
            'tm-employment-details' => [
                'position' => 'P',
                'hoursPerWeek' => 'HPW',
                'id' => 'ID',
                'version' => 'V',
            ],
            'address' => 'ADD',
        ];

        $expected = [
            'id' => 'ID',
            'version' => 'V',
            'employerName' => 'EN',
            'position' => 'P',
            'hoursPerWeek' => 'HPW',
            'address' => 'ADD',
            'transportManager' => 'TM',
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);

        $errors = ['ERROR'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
