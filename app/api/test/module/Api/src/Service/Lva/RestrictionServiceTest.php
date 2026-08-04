<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Lva;

use Dvsa\Olcs\Api\Service\Lva\RestrictionService;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Lva\RestrictionService::class)]
final class RestrictionServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var  RestrictionService */
    protected $helper;

    /**
     * Setup the helper
     */
    public function setUp(): void
    {
        $this->helper = new RestrictionService();
    }

    /**
     * Test isRestrictionSatisfied
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('helper_service')]
    #[\PHPUnit\Framework\Attributes\Group('restriction_helper_service')]
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsRestrictionSatisfied')]
    public function testIsRestrictionSatisfied(mixed $restrictions, mixed $accessKeys, mixed $expected, mixed $ref = null): void
    {
        $this->assertEquals($expected, $this->helper->isRestrictionSatisfied($restrictions, $accessKeys, $ref));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function dpTestIsRestrictionSatisfied(): \Iterator
    {
        //  check callable
        yield [
            'restrictions' => function ($arg) {
                static::assertEquals('unit_Ref', $arg);

                return 'EXPECTED';
            },
            'accessKeys' => [],
            'expected' => 'EXPECTED',
            'ref' => 'unit_Ref',
        ];
        // Really simple restrictions
        yield [
            // We just need to match the string in the array
            'foo',
            ['foo'],
            true
        ];
        yield [
            'foo',
            ['foo', 'bar'],
            true
        ];
        yield [
            'foo',
            [],
            false
        ];
        yield [
            'foo',
            ['bar'],
            false
        ];
        // Simple restrictions
        yield [
            // We can match ANY of the items
            ['foo', 'bar'],
            ['foo'],
            true
        ];
        yield [
            ['foo', 'bar'],
            ['bar'],
            true
        ];
        yield [
            ['foo', 'bar'],
            ['foo', 'bar'],
            true
        ];
        yield [
            ['foo', 'bar'],
            ['foo', 'bar', 'cake'],
            true
        ];
        yield [
            ['foo', 'bar'],
            ['cake', 'fudge'],
            false
        ];
        yield [
            ['foo'],
            ['bar'],
            false
        ];
        yield [
            ['foo'],
            [],
            false
        ];
        // Strict restrictions
        yield [
            [
                // We need to match ALL items in the sub array
                ['foo', 'bar']
            ],
            ['foo', 'bar'],
            true
        ];
        yield [
            [
                ['foo', 'bar', 'cake']
            ],
            ['foo', 'bar'],
            false
        ];
        yield [
            [
                ['foo', 'bar', 'cake']
            ],
            [],
            false
        ];
        // Combination of Strict and Not Strict
        yield [
            [
                // We need to match ALL items in the sub array
                ['foo', 'bar'],
                // Or just this one
                'cake'
            ],
            ['foo', 'bar'],
            true
        ];
        yield [
            [
                ['foo', 'bar'],
                'cake'
            ],
            ['foo', 'bar', 'cake'],
            true
        ];
        yield [
            [
                ['foo', 'bar'],
                'cake'
            ],
            ['foo', 'cake'],
            true
        ];
        yield [
            [
                ['foo', 'bar'],
                'cake'
            ],
            ['cake'],
            true
        ];
        yield [
            [
                ['foo', 'bar'],
                'cake'
            ],
            ['fudge'],
            false
        ];
        yield [
            [
                ['foo', 'bar'],
                'cake'
            ],
            [],
            false
        ];
        // Complex rules
        yield [
            [
                // Must match ALL of these
                [
                    'foo',
                    // This can be satisfied by anything in here
                    ['fudge', 'bar']
                ],
                // Or this one
                'cake'
            ],
            ['foo', 'fudge'],
            true
        ];
        yield [
            [
                [
                    'foo',
                    ['fudge', 'bar']
                ],
                'cake'
            ],
            ['foo', 'bar'],
            true
        ];
        yield [
            [
                [
                    'foo',
                    ['fudge', 'bar']
                ],
                'cake'
            ],
            ['cake'],
            true
        ];
        yield [
            [
                [
                    'foo',
                    ['fudge', 'bar']
                ],
                'cake'
            ],
            ['fudge'],
            false
        ];
        yield [
            [
                [
                    'foo',
                    ['fudge', 'bar']
                ],
                'cake'
            ],
            ['fudge', 'bar'],
            false
        ];
        yield [
            [
                [
                    'foo',
                    ['fudge', 'bar']
                ],
                'cake'
            ],
            ['foo'],
            false
        ];
        yield [
            [
                [
                    'foo',
                    ['fudge', 'bar']
                ],
                'cake'
            ],
            [],
            false
        ];
        yield [
            [
                [
                    ['foo', 'whip'],
                    ['fudge', 'bar']
                ],
                'cake'
            ],
            ['foo', 'bar'],
            true
        ];
        yield [
            [
                [
                    ['foo', 'whip'],
                    ['fudge', 'bar']
                ],
                'cake'
            ],
            ['foo'],
            false
        ];
        // Edge cases
        yield [
            null,
            ['foo'],
            false
        ];
        yield [
            null,
            [],
            false
        ];
        yield [
            null,
            [null],
            false
        ];
    }
}
