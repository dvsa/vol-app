<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\TransportManagerApplication as Sut;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Data\Mapper\TransportManagerApplication::class)]
class TransportManagerApplicationTest extends MockeryTestCase
{
    public function testMapFromResultForTable(): void
    {
        $data = [
            'extra' => [
                'tmApplications' => ['some']
            ]
        ];
        $expected = ['some'];
        $this->assertEquals($expected, Sut::mapFromResultForTable($data));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestMapFromResult')]
    public function testMapFromResult(mixed $inputStatus, mixed $outputStatus): void
    {
        $data = [
            'tmType' => ['id' => 3],
            'tmApplicationStatus' => ['id' => $inputStatus],
            'id' => 5,
            'version' => 6,
            'isOwner' => 1,
            'hasUndertakenTraining' => 'Y',
            'hoursMon' => 1,
            'hoursTue' => 2,
            'hoursWed' => 3,
            'hoursThu' => 4,
            'hoursFri' => 5,
            'hoursSat' => 6,
            'hoursSun' => 7,
            'additionalInformation' => 'ai',
            'application' => 'app',
            'otherLicences' => 'ol'
        ];
        $expected = [
            'details' => [
                'tmType' => ['id' => 3],
                'tmApplicationStatus' => $outputStatus,
                'id' => 5,
                'version' => 6,
                'isOwner' => 1,
                'hasUndertakenTraining' => 'Y',
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
            'application' => 'app'
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }

    public static function dpTestMapFromResult(): array
    {
        return [
            [
                'inputStatus' => RefData::TMA_STATUS_DETAILS_CHECKED,
                'outputStatus' => RefData::TMA_STATUS_INCOMPLETE,
            ],
            [
                'inputStatus' => RefData::TMA_STATUS_DETAILS_SUBMITTED,
                'outputStatus' => RefData::TMA_STATUS_INCOMPLETE,
            ],
            [
                'inputStatus' => RefData::TMA_STATUS_OPERATOR_APPROVED,
                'outputStatus' => RefData::TMA_STATUS_TM_SIGNED,
            ],
        ];
    }

    public function testMapFromFrom(): void
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
                'tmApplicationStatus' => 'as',
                'hasUndertakenTraining' => 'Y',
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
            'tmApplicationStatus' => 'as',
            'hasUndertakenTraining' => 'Y',
            'isOwner' => 1
        ];
        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromErrors(): void
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

        $mockForm = m::mock(\Common\Form\Form::class)
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
