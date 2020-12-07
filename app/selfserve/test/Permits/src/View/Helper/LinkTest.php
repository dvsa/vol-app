<?php

namespace PermitsTest\View\Helper;

use Permits\View\Helper\Link;
use Mockery as m;
use Laminas\View\Renderer\RendererInterface;

/**
 * Class LinkTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LinkTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInvoke()
    {
        $route = 'route';
        $label = 'label';
        $translatedLabel = 'translated label';
        $escapedTranslatedLabel = 'escaped translated label';
        $context = 'context';
        $translatedContext = 'translated context';
        $escapedTranslatedContext = 'escaped translated context';
        $reUseParams = true;
        $linkClass = 'link class';
        $url = 'http://url';

        $output = sprintf(Link::LINK_TEMPLATE, $linkClass, $url, $escapedTranslatedLabel, $escapedTranslatedContext);

        $params = ['params'];
        $options = ['options'];
        
        $view = m::mock(RendererInterface::class);
        $view->shouldReceive('translate')
            ->once()
            ->with($label)
            ->andReturn($translatedLabel);

        $view->shouldReceive('translate')
            ->once()
            ->with($context)
            ->andReturn($translatedContext);

        $view->shouldReceive('escapeHtml')
            ->once()
            ->with($translatedLabel)
            ->andReturn($escapedTranslatedLabel);

        $view->shouldReceive('escapeHtml')
            ->once()
            ->with($translatedContext)
            ->andReturn($escapedTranslatedContext);

        $view->shouldReceive('url')
            ->once()
            ->with($route, $params, $options, $reUseParams)
            ->andReturn($url);

        $sut = new Link();
        $sut->setView($view);

        $actual = $sut->__invoke(
            $route,
            $label,
            $context,
            $params,
            $options,
            $reUseParams,
            $linkClass
        );

        self::assertEquals($output, $actual);
    }
}
