<?php

/**
 * Community Licence Issue No Formetter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\CommunityLicenceIssueNo;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Community Licence Issue No Formetter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommunityLicenceIssueNoTest extends MockeryTestCase
{
    /**
     * @dataProvider formatProvider
     */
    public function testFormat($data, $column, $expected): void
    {
        $sut = new CommunityLicenceIssueNo();
        $this->assertEquals($expected, $sut->format($data, $column));
    }

    /**
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return list{list{array{issueNo: 0}, array{name: 'issueNo'}, '00000 (Office copy)'}, list{array{issueNo: 1}, array{name: 'issueNo'}, '00001'}, list{array{foo: 0}, array{name: 'foo'}, '00000 (Office copy)'}}
     */
    public function formatProvider(): array
    {
        return [
            [
                [
                    'issueNo' => 0
                ],
                [
                    'name' => 'issueNo'
                ],
                '00000 (Office copy)'
            ],
            [
                [
                    'issueNo' => 1
                ],
                [
                    'name' => 'issueNo'
                ],
                '00001'
            ],
            [
                [
                    'foo' => 0
                ],
                [
                    'name' => 'foo'
                ],
                '00000 (Office copy)'
            ]
        ];
    }
}
