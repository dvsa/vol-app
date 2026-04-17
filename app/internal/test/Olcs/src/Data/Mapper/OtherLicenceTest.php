<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\OtherLicence as Sut;
use Laminas\Form\Form;

/**
 * Other Licence Mapper Test
 */
class OtherLicenceTest extends MockeryTestCase
{
    public function testMapFromResult(): void
    {
        $data = ['foo'];
        $expected = [
            'data' => ['foo']
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormProvider')]
    public function testMapFromForm(mixed $data, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public static function mapFromFormProvider(): array
    {
        return [
            [
                // data
                [
                    'data' => [
                        'licNo' => 'licno',
                        'role'  => 'role',
                        'operatingCentres' => 'oc',
                        'totalAuthVehicles' => 'tav',
                        'hoursPerWeek' => 'hpw',
                        'id' => 1,
                        'version' => 2,
                        'redirectId' => 3,
                        'redirectAction' => 'edit-tm-application'
                    ]
                ],
                // expected
                [
                    'licNo' => 'licno',
                    'role'  => 'role',
                    'operatingCentres' => 'oc',
                    'totalAuthVehicles' => 'tav',
                    'hoursPerWeek' => 'hpw',
                    'id' => 1,
                    'version' => 2,
                    'tmaId' => 3
                ]
            ],
            [
                // data
                [
                    'data' => [
                        'licNo' => 'licno',
                        'role'  => 'role',
                        'operatingCentres' => 'oc',
                        'totalAuthVehicles' => 'tav',
                        'hoursPerWeek' => 'hpw',
                        'id' => 1,
                        'version' => 2,
                        'redirectId' => 3,
                        'redirectAction' => 'edit-tm-licence'
                    ]
                ],
                // expected
                [
                    'licNo' => 'licno',
                    'role'  => 'role',
                    'operatingCentres' => 'oc',
                    'totalAuthVehicles' => 'tav',
                    'hoursPerWeek' => 'hpw',
                    'id' => 1,
                    'version' => 2,
                    'tmlId' => 3
                ]
            ]
        ];
    }

    public function testFromErrors(): void
    {
        $formMessages = [
            'data' => [
                'licNo' => ['licno'],
                'role' => ['role'],
                'operatingCentre' => ['oc'],
                'totalAuthVehicles' => ['tav'],
                'hoursPerWeek' => ['hav']
            ]
        ];

        $mockForm = m::mock()
            ->shouldReceive('setMessages')
            ->with($formMessages)
            ->once()
            ->getMock();

        $errors = [
            'licNo' => [
                'isEmpty' => 'licno'
            ],
            'role' => [
                'isEmpty' => 'role'
            ],
            'operatingCentre' => [
                'isEmpty' => 'oc'
            ],
            'totalAuthVehicles' => [
                'isEmpty' => 'tav'
            ],
            'hoursPerWeek' => [
                'isEmpty' => 'hav'
            ],
            'global' => [
                'some' => 'error'
            ]
        ];

        $global = Sut::mapFromErrors($mockForm, $errors);
        $this->assertEquals(['global' => ['some' => 'error']], $global);
    }
}
