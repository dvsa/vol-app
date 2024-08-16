<?php

declare(strict_types=1);

namespace PermitsTest\View\Helper;

use Mockery as m;
use Laminas\View\Renderer\RendererInterface;
use Permits\View\Helper\PermitsCheckAnswerLink;

class PermitsCheckAnswerLinkTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInvoke(): void
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

        $output = sprintf(PermitsCheckAnswerLink::LINK_TEMPLATE, $linkClass, $url, $escapedTranslatedLabel, $escapedTranslatedContext);

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

        $sut = new PermitsCheckAnswerLink();
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
