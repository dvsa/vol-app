<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\NavigationParentPage;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\View\Helper\NavigationParentPage
 */
class NavigationParentPageTest extends MockeryTestCase
{
    /** @var  m\MockInterface */
    private $mockBreadcrumbs;

    /** @var  \Laminas\View\Renderer\RendererInterface */
    private $mockView;

    /**
     * Setup the view helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->mockBreadcrumbs = m::mock();

        $this->mockView = m::mock(\Laminas\View\Renderer\RendererInterface::class);
        $this->mockView->shouldReceive('navigation->breadcrumbs')->once()->andReturn($this->mockBreadcrumbs);
    }

    /**
     * @dataProvider dpTestInvoke
     */
    public function testInvoke($activePage, $expect): void
    {
        $mockContainer = m::mock();

        $this->mockBreadcrumbs
            ->shouldReceive('getContainer')->once()->andReturn($mockContainer)
            ->shouldReceive('findActive')->once()->with($mockContainer)->andReturn($activePage);

        $sut = (new NavigationParentPage())
            ->setView($this->mockView);

        static::assertEquals($expect, $sut->__invoke());
    }

    /**
     * @return (array|null|string)[][]
     *
     * @psalm-return list{array{activePage: array<never, never>, expect: null}, array{activePage: array{page: mixed}, expect: 'EXPECT'}}
     */
    public function dpTestInvoke(): array
    {
        return [
            [
                'activePage' => [],
                'expect' => null,
            ],
            [
                'activePage' => [
                    'page' => m::mock(\Laminas\Navigation\Page\Mvc::class)
                        ->shouldReceive('getParent')->atMost(1)->andReturn('EXPECT')
                        ->getMock(),
                ],
                'expect' => 'EXPECT',
            ],
        ];
    }
}
