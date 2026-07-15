<?php

/**
 * LicenceNumberLinkTest.php
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\LicenceNumberLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class LicenceNumberLinkTest
 *
 * @package CommonTest\Service\Table\Formatter
 */
final class LicenceNumberLinkTest extends TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new LicenceNumberLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $expected): void
    {
        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'lva-licence',
                [
                    'licence' => $data['licence']['id']
                ]
            )
            ->andReturn('LICENCE_URL');

        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    /**
     * @return \Iterator<(int | string), array<(array<array<(int | string)>> | string)>>
     *
     * @psalm-return list{list{array{licence: array{id: 1, licNo: 1, status: 'lsts_valid'}}, '<a class="govuk-link" href="LICENCE_URL">1</a>'}, list{array{licence: array{id: 1, licNo: 1, status: 'not-valid'}}, '1'}}
     */
    public static function formatProvider(): \Iterator
    {
        yield [
            [
                'licence' => [
                    'id' => 1,
                    'licNo' => 0001,
                    'status' => 'lsts_valid'
                ]
            ],
            '<a class="govuk-link" href="LICENCE_URL">1</a>'
        ];
        yield [
            [
                'licence' => [
                    'id' => 1,
                    'licNo' => 0001,
                    'status' => 'not-valid'
                ]
            ],
            '1'
        ];
    }
}
