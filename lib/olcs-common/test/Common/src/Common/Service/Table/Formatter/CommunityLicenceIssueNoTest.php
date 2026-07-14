<?php

/**
 * Community Licence Issue No Formetter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\CommunityLicenceIssueNo;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Community Licence Issue No Formetter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CommunityLicenceIssueNoTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $column, $expected): void
    {
        $sut = new CommunityLicenceIssueNo();
        $this->assertEquals($expected, $sut->format($data, $column));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(int | string)> | string)>>
     *
     * @psalm-return list{list{array{issueNo: 0}, array{name: 'issueNo'}, '00000 (Office copy)'}, list{array{issueNo: 1}, array{name: 'issueNo'}, '00001'}, list{array{foo: 0}, array{name: 'foo'}, '00000 (Office copy)'}}
     */
    public static function formatProvider(): \Iterator
    {
        yield [
            [
                'issueNo' => 0
            ],
            [
                'name' => 'issueNo'
            ],
            '00000 (Office copy)'
        ];
        yield [
            [
                'issueNo' => 1
            ],
            [
                'name' => 'issueNo'
            ],
            '00001'
        ];
        yield [
            [
                'foo' => 0
            ],
            [
                'name' => 'foo'
            ],
            '00000 (Office copy)'
        ];
    }
}
