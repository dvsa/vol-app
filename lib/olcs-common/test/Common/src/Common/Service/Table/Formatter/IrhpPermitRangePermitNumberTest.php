<?php

/**
 * Irhp Permit Range Permit Number Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Service\Table\Formatter\IrhpPermitRangePermitNumber;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class IrhpPermitRangePermitNumberTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelper::class);
        $this->sut = new IrhpPermitRangePermitNumber($this->urlHelper);
    }

    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('IrhpPermitSectorFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $expected): void
    {

        $this->urlHelper->shouldReceive('fromRoute')
            ->with(
                'admin-dashboard/admin-permits/ranges',
                [
                    'stockId' => '1',
                    'action' => 'edit',
                    'id' => '1'
                ]
            )
            ->andReturn('WINDOW_EDIT_URL');

        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function formatProvider(): \Iterator
    {
        yield [
            'data' => [
                'prefix' => '',
                'fromNo' => '1',
                'toNo' => '2',
                'irhpPermitStock' => [
                    'id' => '1'
                ],
                'id' => '1'
            ],
            'expected' => "<a class='govuk-link js-modal-ajax' href='WINDOW_EDIT_URL'>1 to 2</a>",
        ];
        yield [
            'data' => [
                'prefix' => 'UK',
                'fromNo' => '1',
                'toNo' => '2',
                'irhpPermitStock' => [
                    'id' => '1'
                ],
                'id' => '1'
            ],
            'expected' => "<a class='govuk-link js-modal-ajax' href='WINDOW_EDIT_URL'>UK1 to UK2</a>",
        ];
    }
}
