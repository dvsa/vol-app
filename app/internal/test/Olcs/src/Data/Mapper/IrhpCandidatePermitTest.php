<?php

namespace OlcsTest\Data\Mapper;

use Olcs\Data\Mapper\IrhpCandidatePermit;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpCandidatePermitTest
 */
class IrhpCandidatePermitTest extends MockeryTestCase
{
    private $sut;

    public function setUp(): void
    {
        $this->sut = new IrhpCandidatePermit();
    }

    /**
     * @dataProvider dpMapApplicationData
     */
    public function testMapApplicationData($data, $expected)
    {
        $this->assertSame($expected, $this->sut->mapApplicationData($data));
    }

    public function dpMapApplicationData()
    {
        return [
            [
                [
                    'irhpPermitApplications' => [
                        [
                            'requiredEuro5' => 2,
                            'requiredEuro6' => 3,
                        ],
                    ],
                    'countrys' => null,
                ],
                [
                    'requiredEuro5' => 2,
                    'requiredEuro6' => 3,
                    'countries' => '',
                ],
            ],
            [
                [
                    'irhpPermitApplications' => [
                        [
                            'requiredEuro5' => 2,
                            'requiredEuro6' => 3,
                        ],
                    ],
                    'countrys' => [
                        [
                            'id' => 'NL',
                            'countryDesc' => 'Netherlands',
                        ],
                    ],
                ],
                [
                    'requiredEuro5' => 2,
                    'requiredEuro6' => 3,
                    'countries' => 'Netherlands',
                ],
            ],
            [
                [
                    'irhpPermitApplications' => [
                        [
                            'requiredEuro5' => 2,
                            'requiredEuro6' => 3,
                        ],
                    ],
                    'countrys' => [
                        [
                            'id' => 'IT',
                            'countryDesc' => 'Italy',
                        ],
                        [
                            'id' => 'NL',
                            'countryDesc' => 'Netherlands',
                        ],
                    ],
                ],
                [
                    'requiredEuro5' => 2,
                    'requiredEuro6' => 3,
                    'countries' => 'Italy, Netherlands',
                ],
            ],
        ];
    }
}
