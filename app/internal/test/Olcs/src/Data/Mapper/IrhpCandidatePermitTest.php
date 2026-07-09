<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Olcs\Data\Mapper\IrhpCandidatePermit;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpCandidatePermitTest
 */
final class IrhpCandidatePermitTest extends MockeryTestCase
{
    private $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->sut = new IrhpCandidatePermit();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpMapApplicationData')]
    public function testMapApplicationData(mixed $data, mixed $expected): void
    {
        $this->assertSame($expected, $this->sut->mapApplicationData($data));
    }

    public static function dpMapApplicationData(): \Iterator
    {
        yield [
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
        ];
        yield [
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
        ];
        yield [
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
        ];
    }
}
