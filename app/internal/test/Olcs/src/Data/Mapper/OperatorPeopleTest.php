<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\OperatorBusinessDetails as Sut;
use Laminas\Form\Form;

/**
 * OperatorPeopleTest
 */
class OperatorPeopleTest extends MockeryTestCase
{
    public function setUp(): void
    {
        $this->sut = new \Olcs\Data\Mapper\OperatorPeople();
    }

    public function testMapFromResult()
    {
        $data = [
            'organisation' => 23,
            'id' => 24,
            'version' => 54,
            'position' => 'POSITION',
            'person' => [
                'title' => [
                    'id' => 'TITLE_ID'
                ],
                'forename' => 'FRED',
                'familyName' => 'SMITH',
                'otherName' => 'FREDDY',
                'birthDate' => '1966-06-21'
            ]
        ];

        $expected = [
            'organisation' => 23,
            'data' => [
                'id' => 24,
                'version' => 54,
                'title' => 'TITLE_ID',
                'forename' => 'FRED',
                'familyName' => 'SMITH',
                'otherName' => 'FREDDY',
                'position' => 'POSITION',
                'birthDate' => [
                    'day' => '21',
                    'month' => '06',
                    'year' => '1966',
                ],
            ]
        ];

        $this->assertSame($expected, $this->sut->mapFromResult($data));
    }

    public function testMapFromResultNoBirthDate()
    {
        $data = [
            'organisation' => 23,
            'id' => 24,
            'version' => 54,
            'position' => 'POSITION',
            'person' => [
                'title' => [
                    'id' => 'TITLE_ID'
                ],
                'forename' => 'FRED',
                'familyName' => 'SMITH',
                'otherName' => 'FREDDY',
            ]
        ];

        $expected = [
            'organisation' => 23,
            'data' => [
                'id' => 24,
                'version' => 54,
                'title' => 'TITLE_ID',
                'forename' => 'FRED',
                'familyName' => 'SMITH',
                'otherName' => 'FREDDY',
                'position' => 'POSITION',
            ]
        ];

        $this->assertSame($expected, $this->sut->mapFromResult($data));
    }

    public function testMapFromForm()
    {
        $data = [
            'organisation' => 23,
            'data' => [
                'id' => 24,
                'version' => 54,
                'title' => 'TITLE_ID',
                'forename' => 'FRED',
                'familyName' => 'SMITH',
                'otherName' => 'FREDDY',
                'position' => 'POSITION',
                'birthDate' => '1966-08-12',
            ]
        ];

        $expected = [
            'id' => 24,
            'version' => 54,
            'person' => [
                'title' => 'TITLE_ID',
                'forename' => 'FRED',
                'familyName' => 'SMITH',
                'otherName' => 'FREDDY',
                'birthDate' => '1966-08-12',
            ],
            'organisation' => 23,
            'position' => 'POSITION',
        ];

        $this->assertSame($expected, $this->sut->mapFromForm($data));
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(\Laminas\Form\FormInterface::class);
        $this->assertSame(['ERRORS'], $this->sut->mapFromErrors($mockForm, ['ERRORS']));
    }
}
