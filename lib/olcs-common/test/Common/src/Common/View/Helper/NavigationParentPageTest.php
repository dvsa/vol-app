<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\NavigationParentPage;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\View\Helper\NavigationParentPage::class)]
final class NavigationParentPageTest extends MockeryTestCase
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestInvoke')]
    public function testInvoke($activePage, $expect): void
    {
        $mockContainer = m::mock();

        $this->mockBreadcrumbs
            ->shouldReceive('getContainer')->once()->andReturn($mockContainer)
            ->shouldReceive('findActive')->once()->with($mockContainer)->andReturn($activePage);

        $sut = new NavigationParentPage()
            ->setView($this->mockView);

        $this->assertEquals($expect, $sut->__invoke());
    }

    /**
     * @return \Iterator<(int | string), array<(array<mixed> | string | null)>>
     *
     * @psalm-return list{array{activePage: array<never, never>, expect: null}, array{activePage: array{page: mixed}, expect: 'EXPECT'}}
     */
    public static function dpTestInvoke(): \Iterator
    {
        yield [
            'activePage' => [],
            'expect' => null,
        ];
        yield [
            'activePage' => [
                'page' => m::mock(\Laminas\Navigation\Page\Mvc::class)
                    ->shouldReceive('getParent')->atMost(1)->andReturn('EXPECT')
                    ->getMock(),
            ],
            'expect' => 'EXPECT',
        ];
    }
}
