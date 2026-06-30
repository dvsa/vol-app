<?php

/**
 * Restriction Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\RestrictionHelperService;

/**
 * Restriction Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RestrictionHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    public $helper;
    /**
     * Setup the helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->helper = new RestrictionHelperService();
    }

    /**
     * Test isRestrictionSatisfied
     *
     * @dataProvider isRestrictionSatisfiedProvider
     * @group helper_service
     * @group restriction_helper_service
     */
    public function testIsRestrictionSatisfied($restrictions, $accessKeys, $expected): void
    {
        $this->assertEquals($expected, $this->helper->isRestrictionSatisfied($restrictions, $accessKeys));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function isRestrictionSatisfiedProvider()
    {
        return [
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
