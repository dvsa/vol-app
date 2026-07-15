<?php

/**
 * Task Owner Formatter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\TaskOwner;

/**
 * Task Owner Formatter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class TaskOwnerTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('providerFormat')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new TaskOwner()->format($data));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(string | null)> | string)>>
     *
     * @psalm-return list{list{array{teamName: null, ownerName: ' '}, '(Unassigned)'}, list{array{teamName: 'Footeam', ownerName: ' '}, 'Footeam (Unassigned)'}, list{array{teamName: null, ownerName: 'Foo'}, '(Foo)'}, list{array{teamName: 'Foo', ownerName: 'Bar'}, 'Foo (Bar)'}, list{array{teamName: 'Footeam', ownerName: ','}, 'Footeam (Unassigned)'}}
     */
    public static function providerFormat(): \Iterator
    {
        yield [
            [
                'teamName' => null,
                'ownerName' => ' '
            ],
            '(Unassigned)'
        ];
        yield [
            [
                'teamName' => 'Footeam',
                'ownerName' => ' '
            ],
            'Footeam (Unassigned)'
        ];
        yield [
            [
                'teamName' => null,
                'ownerName' => 'Foo'
            ],
            '(Foo)'
        ];
        yield [
            [
                'teamName' => 'Foo',
                'ownerName' => 'Bar'
            ],
            'Foo (Bar)'
        ];
        yield [
            [
                'teamName' => 'Footeam',
                'ownerName' => ','
            ],
            'Footeam (Unassigned)'
        ];
    }
}
