<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\PageTitle;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\I18n\View\Helper\Translate;
use Laminas\Router\Http\RouteMatch;
use Laminas\View\Helper\Placeholder;

/**
 * @covers Common\View\Helper\PageTitle
 */
class PageTitleTest extends MockeryTestCase
{
    public $placeholder;
    public $translate;
    public $routeMatch;
    #[\Override]
    protected function setUp(): void
    {
        $placeholder = m::mock(Placeholder::class);
        $this->placeholder = $placeholder;

        $translate = m::mock(Translate::class);
        $routeMatch = m::mock(RouteMatch::class);
        $this->translate = $translate;
        $this->routeMatch = $routeMatch;
    }

    /**
     * @dataProvider providerInvoke
     */
    public function testInvoke($pageTitlePlaceholder, $matchedRouteName, $keyToTranslate): void
    {
        $this->routeMatch->shouldReceive('getMatchedRouteName')->andReturn($matchedRouteName);
        $this->routeMatch->shouldReceive('getParam')->with('action')->andReturn('someaction');
        $this->translate->shouldReceive('__invoke')->with($keyToTranslate)->andReturn('translated');

        $this->placeholder->shouldReceive('getContainer')->with('pageTitle')->andReturn($pageTitlePlaceholder);

        $app = m::mock();
        $app->shouldReceive('getMvcEvent->getRouteMatch')->andReturn($this->routeMatch);

        /** @var ContainerInterface | m\MockInterface $sm */
        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with('Application')->andReturn($app);

        $sut = new PageTitle($this->translate, $this->placeholder, $matchedRouteName, 'someaction');

        $this->assertEquals('translated', $sut->__invoke());
    }

    /**
     * @return (null|string)[][]
     *
     * @psalm-return array{placeholder: list{'placeholder', 'foo/bar', 'placeholder'}, routingWithTranslation: list{null, 'foo/bar', 'page.title.foo/bar.someaction'}, routingWithoutTranslation: list{null, null, null}}
     */
    public function providerInvoke(): array
    {
        return [
            'placeholder' => [
                'placeholder',
                'foo/bar',
                'placeholder',
            ],
            'routingWithTranslation' => [
                null,
                'foo/bar',
                'page.title.foo/bar.someaction',
            ],
            'routingWithoutTranslation' => [
                null,
                null,
                null,
            ],
        ];
    }
}
