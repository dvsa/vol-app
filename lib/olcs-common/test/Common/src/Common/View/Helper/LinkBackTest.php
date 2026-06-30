<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\LinkBack;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\View\Helper\LinkBack
 */
class LinkBackTest extends MockeryTestCase
{
    /** @var  m\MockInterface */
    private $mockRequest;

    /** @var  \Laminas\View\Renderer\RendererInterface */
    private $mockView;

    /**
     * Setup the view helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->mockView = m::mock(\Laminas\View\Renderer\RendererInterface::class)
            ->shouldReceive('translate')
            ->zeroOrMoreTimes()
            ->andReturnUsing(
                static fn($arg) => '_TRLTD_' . $arg
            )
            ->getMock();

        $this->mockRequest = m::mock(\Laminas\Http\Request::class);
    }

    /**
     * @dataProvider dpTestInvoke
     */
    public function testInvoke($params, $referer, $expect): void
    {
        if ($referer !== null) {
            $this->mockRequest->shouldReceive('getHeader')->once()->with('referer')->andReturn($referer);
        }

        $sut = (new LinkBack($this->mockRequest))
            ->setView($this->mockView);

        static::assertEquals($expect, $sut->__invoke($params));
    }

    /**
     * @return ((false|string)[]|false|m\LegacyMockInterface&m\MockInterface&\Laminas\Http\Header\HeaderInterface|null|string)[][]
     *
     * @psalm-return list{array{params: null, referer: false, expect: ''}, array{params: null, referer: m\LegacyMockInterface&m\MockInterface&\Laminas\Http\Header\HeaderInterface, expect: '<a href="unit_URL" class="govuk-back-link">_TRLTD_common.link.back.label</a>'}, array{params: array{label: 'unit_PrmLbl', url: 'unit_PrmUrl2', escape: false}, referer: null, expect: '<a href="unit_PrmUrl2" class="govuk-back-link">_TRLTD_unit_PrmLbl</a>'}}
     */
    public function dpTestInvoke(): array
    {
        $mockHeader = m::mock(\Laminas\Http\Header\HeaderInterface::class);
        $mockHeader->shouldReceive('uri->getPath')->atMost(1)->andReturn('unit_URL');

        return [
            //  parameter not set, no referer page
            [
                'params' => null,
                'referer' => false,
                'expect' => '',
            ],
            //  parameter not set, has referer page
            [
                'params' => null,
                'referer' => $mockHeader,
                'expect' => '<a href="unit_URL" class="govuk-back-link">_TRLTD_common.link.back.label</a>',
            ],
            //  parameter not set, has referer page
            [
                'params' => [
                    'label' => 'unit_PrmLbl',
                    'url' => 'unit_PrmUrl2',
                    'escape' => false,
                ],
                'referer' => null,
                'expect' => '<a href="unit_PrmUrl2" class="govuk-back-link">_TRLTD_unit_PrmLbl</a>',
            ],
        ];
    }
}
