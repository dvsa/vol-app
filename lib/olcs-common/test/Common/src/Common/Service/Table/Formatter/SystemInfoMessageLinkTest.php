<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\SystemInfoMessageLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * @covers Common\Service\Table\Formatter\SystemInfoMessageLink
 */
class SystemInfoMessageLinkTest extends TestCase
{
    private const EXPECT_URL = 'unit_Url';

    private const ID = 9999;

    protected $urlHelper;

    protected $translator;

    protected $viewHelperManager;

    protected $router;

    protected $request;

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


    /**
     * @dataProvider dpTestFormat
     */
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

        static::assertEquals(
            $expect,
            $this->sut->format($data, [])
        );
    }

    /**
     * @return ((bool|string)[]|string)[][]
     *
     * @psalm-return list{array{data: array{description: 'unit_Desc', isActive: true}, expect: '<a href="unit_Url" class="govuk-link js-modal-ajax">unit_Desc</a> <span class="status green">ACTIVE</span>'}, array{data: array{description: string, isActive: false}, expect: string}}
     */
    public function dpTestFormat(): array
    {
        return [
            [
                'data' => [
                    'description' => 'unit_Desc',
                    'isActive' => true,
                ],
                'expect' => '<a href="' . self::EXPECT_URL . '" class="govuk-link js-modal-ajax">unit_Desc</a>' .
                    ' <span class="status green">ACTIVE</span>',
            ],
            [
                'data' => [
                    'description' => str_repeat('X', SystemInfoMessageLink::MAX_DESC_LEN + 1),
                    'isActive' => false,
                ],
                'expect' =>
                    '<a href="' . self::EXPECT_URL . '" class="govuk-link js-modal-ajax">' .
                    str_repeat('X', SystemInfoMessageLink::MAX_DESC_LEN) . '...' .
                    '</a>' .
                    ' <span class="status grey">INACTIVE</span>',
            ],
        ];
    }
}
