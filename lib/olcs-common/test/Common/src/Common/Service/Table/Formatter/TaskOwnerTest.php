<?php

/**
 * Task Owner Formatter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\TaskOwner;

/**
 * Task Owner Formatter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaskOwnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider providerFormat
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new TaskOwner())->format($data));
    }

    /**
     * @return ((null|string)[]|string)[][]
     *
     * @psalm-return list{list{array{teamName: null, ownerName: ' '}, '(Unassigned)'}, list{array{teamName: 'Footeam', ownerName: ' '}, 'Footeam (Unassigned)'}, list{array{teamName: null, ownerName: 'Foo'}, '(Foo)'}, list{array{teamName: 'Foo', ownerName: 'Bar'}, 'Foo (Bar)'}, list{array{teamName: 'Footeam', ownerName: ','}, 'Footeam (Unassigned)'}}
     */
    public function providerFormat(): array
    {
        return [
            [
                [
                    'teamName' => null,
                    'ownerName' => ' '
                ],
                '(Unassigned)'
            ],
            [
                [
                    'teamName' => 'Footeam',
                    'ownerName' => ' '
                ],
                'Footeam (Unassigned)'
            ],
            [
                [
                    'teamName' => null,
                    'ownerName' => 'Foo'
                ],
                '(Foo)'
            ],
            [
                [
                    'teamName' => 'Foo',
                    'ownerName' => 'Bar'
                ],
                'Foo (Bar)'
            ],
            [
                [
                    'teamName' => 'Footeam',
                    'ownerName' => ','
                ],
                'Footeam (Unassigned)'
            ],
        ];
    }
}
