<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\SystemInfoMessageLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Service\Table\Formatter\SystemInfoMessageLink::class)]
final class SystemInfoMessageLinkTest extends TestCase
{
    private const string EXPECT_URL = 'unit_Url';

    private const int ID = 9999;

    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new SystemInfoMessageLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFormat')]
    public function testFormat($data, $expect): void
    {
        $data['id'] = self::ID;

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'admin-dashboard/admin-system-info-message',
                [
                    'action' => 'edit',
                    'msgId' => self::ID,
                ]
            )
            ->andReturn(self::EXPECT_URL);

        $this->assertEquals($expect, $this->sut->format($data, []));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(bool | string)> | string)>>
     *
     * @psalm-return list{array{data: array{description: 'unit_Desc', isActive: true}, expect: '<a href="unit_Url" class="govuk-link js-modal-ajax">unit_Desc</a> <span class="status green">ACTIVE</span>'}, array{data: array{description: string, isActive: false}, expect: string}}
     */
    public static function dpTestFormat(): \Iterator
    {
        yield [
            'data' => [
                'description' => 'unit_Desc',
                'isActive' => true,
            ],
            'expect' => '<a href="' . self::EXPECT_URL . '" class="govuk-link js-modal-ajax">unit_Desc</a>' .
                ' <span class="status green">ACTIVE</span>',
        ];
        yield [
            'data' => [
                'description' => str_repeat('X', SystemInfoMessageLink::MAX_DESC_LEN + 1),
                'isActive' => false,
            ],
            'expect' =>
                '<a href="' . self::EXPECT_URL . '" class="govuk-link js-modal-ajax">' .
                str_repeat('X', SystemInfoMessageLink::MAX_DESC_LEN) . '...' .
                '</a>' .
                ' <span class="status grey">INACTIVE</span>',
        ];
    }
}
