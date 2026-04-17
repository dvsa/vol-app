<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Lva;

use Dvsa\Olcs\Api\Service\Lva\RestrictionService;

/**
 * @covers Dvsa\Olcs\Api\Service\Lva\RestrictionService
 */
class RestrictionServiceTest extends \PHPUnit\Framework\TestCase
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
     * @return array
     */
    public static function dpTestIsRestrictionSatisfied(): array
    {
        return [
            //  check callable
            [
                'restrictions' => function ($arg) {
                    static::assertEquals($arg, 'unit_Ref');

                    return 'EXPECTED';
                },
                'accessKeys' => [],
                'expected' => 'EXPECTED',
                'ref' => 'unit_Ref',
            ],
            // Really simple restrictions
            [
                // We just need to match the string in the array
                'foo',
                ['foo'],
                true
            ],
            [
                'foo',
                ['foo', 'bar'],
                true
            ],
            [
                'foo',
                [],
                false
            ],
            [
                'foo',
                ['bar'],
                false
            ],
            // Simple restrictions
            [
                // We can match ANY of the items
                ['foo', 'bar'],
                ['foo'],
                true
            ],
            [
                ['foo', 'bar'],
                ['bar'],
                true
            ],
            [
                ['foo', 'bar'],
                ['foo', 'bar'],
                true
            ],
            [
                ['foo', 'bar'],
                ['foo', 'bar', 'cake'],
                true
            ],
            [
                ['foo', 'bar'],
                ['cake', 'fudge'],
                false
            ],
            [
                ['foo'],
                ['bar'],
                false
            ],
            [
                ['foo'],
                [],
                false
            ],
            // Strict restrictions
            [
                [
                    // We need to match ALL items in the sub array
                    ['foo', 'bar']
                ],
                ['foo', 'bar'],
                true
            ],
            [
                [
                    ['foo', 'bar', 'cake']
                ],
                ['foo', 'bar'],
                false
            ],
            [
                [
                    ['foo', 'bar', 'cake']
                ],
                [],
                false
            ],
            // Combination of Strict and Not Strict
            [
                [
                    // We need to match ALL items in the sub array
                    ['foo', 'bar'],
                    // Or just this one
                    'cake'
                ],
                ['foo', 'bar'],
                true
            ],
            [
                [
                    ['foo', 'bar'],
                    'cake'
                ],
                ['foo', 'bar', 'cake'],
                true
            ],
            [
                [
                    ['foo', 'bar'],
                    'cake'
                ],
                ['foo', 'cake'],
                true
            ],
            [
                [
                    ['foo', 'bar'],
                    'cake'
                ],
                ['cake'],
                true
            ],
            [
                [
                    ['foo', 'bar'],
                    'cake'
                ],
                ['fudge'],
                false
            ],
            [
                [
                    ['foo', 'bar'],
                    'cake'
                ],
                [],
                false
            ],
            // Complex rules
            [
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
            ],
            [
                [
                    [
                        'foo',
                        ['fudge', 'bar']
                    ],
                    'cake'
                ],
                ['foo', 'bar'],
                true
            ],
            [
                [
                    [
                        'foo',
                        ['fudge', 'bar']
                    ],
                    'cake'
                ],
                ['cake'],
                true
            ],
            [
                [
                    [
                        'foo',
                        ['fudge', 'bar']
                    ],
                    'cake'
                ],
                ['fudge'],
                false
            ],
            [
                [
                    [
                        'foo',
                        ['fudge', 'bar']
                    ],
                    'cake'
                ],
                ['fudge', 'bar'],
                false
            ],
            [
                [
                    [
                        'foo',
                        ['fudge', 'bar']
                    ],
                    'cake'
                ],
                ['foo'],
                false
            ],
            [
                [
                    [
                        'foo',
                        ['fudge', 'bar']
                    ],
                    'cake'
                ],
                [],
                false
            ],
            [
                [
                    [
                        ['foo', 'whip'],
                        ['fudge', 'bar']
                    ],
                    'cake'
                ],
                ['foo', 'bar'],
                true
            ],
            [
                [
                    [
                        ['foo', 'whip'],
                        ['fudge', 'bar']
                    ],
                    'cake'
                ],
                ['foo'],
                false
            ],
            // Edge cases
            [
                null,
                ['foo'],
                false
            ],
            [
                null,
                [],
                false
            ],
            [
                null,
                [null],
                false
            ],
        ];
    }
}
