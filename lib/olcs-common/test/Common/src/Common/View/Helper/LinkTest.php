<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\Link;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see Link
 */
final class LinkTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $linkText = 'link text';
        $translatedLinkText = 'translated link text';
        $translatedLinkTextEscaped = 'escaped translated link text';
        $linkClass = 'link class';
        $linkClassEscaped = 'escaped link class';
        $url = 'http://url';
        $urlEscaped = 'http://url/escaped';

        $output = '<a href="' . $urlEscaped . '" class="' . $linkClassEscaped . '">' . $translatedLinkTextEscaped . '</a>';

        $view = m::mock(RendererInterface::class);
        $view->expects('translate')
            ->with($linkText)
            ->andReturn($translatedLinkText);

        $view->expects('escapeHtml')
            ->with($translatedLinkText)
            ->andReturn($translatedLinkTextEscaped);

        $view->expects('escapeHtmlAttr')
            ->with($url)
            ->andReturn($urlEscaped);

        $view->expects('escapeHtmlAttr')
            ->with($linkClass)
            ->andReturn($linkClassEscaped);

        $sut = new Link();
        $sut->setView($view);

        $this->assertSame($output, $sut->__invoke($url, $linkText, $linkClass));
    }
}
