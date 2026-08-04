<?php

/**
 * Case Link Test
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\CaseLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Case Link Test
 *
 * @package CommonTest\Service\Table\Formatter
 */
final class CaseLinkTest extends TestCase
{
    public $sut;
    protected $urlHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new CaseLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->urlHelper->shouldReceive('fromRoute')
            ->with(
                'case',
                [
                    'case' => 69,
                ]
            )
            ->andReturn('CASE_URL');

        $this->assertEquals(
            $expected,
            $this->sut->format($data, [])
        );
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'case' => [
            [
                'id' => 69
            ],
            '<a class="govuk-link" href="CASE_URL">69</a>',
        ];
        yield 'other' => [
            [],
            '',
        ];
    }
}
