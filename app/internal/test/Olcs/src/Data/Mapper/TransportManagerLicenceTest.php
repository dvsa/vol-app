<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\TransportManagerLicence as Sut;

/**
 * @covers \Olcs\Data\Mapper\TransportManagerLicence
 */
class TransportManagerLicenceTest extends MockeryTestCase
{
    public function testMapFromResultForTable()
    {
        $data = [
            'results' => ['res']
        ];
        $expected = ['res'];
        $this->assertEquals($expected, Sut::mapFromResultForTable($data));
    }

    public function testMapFromResult()
    {
        $data = [
            'tmType' => ['id' => 3],
            'id' => 5,
            'version' => 6,
            'isOwner' => 1,
            'hoursMon' => 1,
            'hoursTue' => 2,
            'hoursWed' => 3,
            'hoursThu' => 4,
            'hoursFri' => 5,
            'hoursSat' => 6,
            'hoursSun' => 7,
            'additionalInformation' => 'ai',
            'licence' => 'lic',
            'otherLicences' => 'ol'
        ];
        $expected = [
            'details' => [
                'tmType' => ['id' => 3],
                'id' => 5,
                'version' => 6,
                'isOwner' => 1,
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 1,
                        'hoursTue' => 2,
                        'hoursWed' => 3,
                        'hoursThu' => 4,
                        'hoursFri' => 5,
                        'hoursSat' => 6,
                        'hoursSun' => 7
                    ]
                ],
                'additionalInformation' => 'ai'
            ],
            'otherLicences' => 'ol',
            'licence' => 'lic'
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public function testMapFromEmptyResult()
    {
        $this->assertEquals([], Sut::mapFromResult([]));
    }

    public function testMapFromFrom()
    {
        $data = [
            'details' => [
                'id' => 1,
                'version' => 2,
                'tmType' => 3,
                'additionalInformation' => 'ai',
                'hoursOfWeek' => [
                    'hoursPerWeekContent' => [
                        'hoursMon' => 1,
                        'hoursTue' => 2,
                        'hoursWed' => 3,
                        'hoursThu' => 4,
                        'hoursFri' => 5,
                        'hoursSat' => 6,
                        'hoursSun' => 7
                    ]
                ],
                'isOwner' => 1
            ]
        ];
        $expected = [
            'id' => 1,
            'version' => 2,
            'tmType' => 3,
            'additionalInformation' => 'ai',
            'hoursMon' => 1,
            'hoursTue' => 2,
            'hoursWed' => 3,
            'hoursThu' => 4,
            'hoursFri' => 5,
            'hoursSat' => 6,
            'hoursSun' => 7,
            'isOwner' => 1
        ];
        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromErrors()
    {
        $formMessages = [
            'details' => [
                'tmType' => [['isEmpty' => 'tmType']],
                'additionalInformation' => [['isEmpty' => 'additionalInformation']],
            ],
            'hoursOfWeek' => [
                'hoursPerWeekContent' => [
                    'hoursMon' => [['isEmpty' => 'hoursMon']],
                    'hoursTue' => [['isEmpty' => 'hoursTue']],
                    'hoursWed' => [['isEmpty' => 'hoursWed']],
                    'hoursThu' => [['isEmpty' => 'hoursThu']],
                    'hoursFri' => [['isEmpty' => 'hoursFri']],
                    'hoursSat' => [['isEmpty' => 'hoursSat']],
                    'hoursSun' => [['isEmpty' => 'hoursSun']]
                ]
            ]
        ];

        $mockForm = m::mock()
            ->shouldReceive('setMessages')
            ->with($formMessages)
            ->once()
            ->getMock();

        $errors = [
            'tmType' => [
                'isEmpty' => 'tmType'
            ],
            'additionalInformation' => [
                'isEmpty' => 'additionalInformation'
            ],
            'hoursMon' => [
                'isEmpty' => 'hoursMon'
            ],
            'hoursTue' => [
                'isEmpty' => 'hoursTue'
            ],
            'hoursWed' => [
                'isEmpty' => 'hoursWed'
            ],
            'hoursThu' => [
                'isEmpty' => 'hoursThu'
            ],
            'hoursFri' => [
                'isEmpty' => 'hoursFri'
            ],
            'hoursSat' => [
                'isEmpty' => 'hoursSat'
            ],
            'hoursSun' => [
                'isEmpty' => 'hoursSun'
            ],
            'global' => [
                'some' => 'error'
            ]
        ];

        $global = Sut::mapFromErrors($mockForm, $errors);
        $this->assertEquals(['global' => ['some' => 'error']], $global);
    }
}
