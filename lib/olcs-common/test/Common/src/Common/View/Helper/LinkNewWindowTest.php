<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\LinkNewWindow;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see LinkNewWindow
 */
final class LinkNewWindowTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsExternalLink')]
    public function testInvoke(bool $hideNewTabMessage, bool $isExternal, string $output): void
    {
        $linkText = 'link text';
        $translatedLinkText = 'translated link text';
        $translatedLinkTextEscaped = 'escaped translated link text';
        $translatedNewTabText = 'translated new tab text';
        $translatedNewTabTextEscaped = 'escaped translated new tab text';
        $linkClass = 'link class';
        $linkClassEscaped = 'escaped link class';
        $url = 'http://url';
        $urlEscaped = 'http://url/escaped';

        $view = m::mock(RendererInterface::class);
        $view->expects('translate')
            ->with($linkText)
            ->andReturn($translatedLinkText);

        $view->expects('escapeHtml')
            ->with($translatedLinkText)
            ->andReturn($translatedLinkTextEscaped);

        $view->expects('translate')
            ->with(LinkNewWindow::NEW_TAB_MESSAGE)
            ->andReturn($translatedNewTabText);

        $view->expects('escapeHtml')
            ->with($translatedNewTabText)
            ->andReturn($translatedNewTabTextEscaped);

        $view->expects('escapeHtmlAttr')
            ->with($url)
            ->andReturn($urlEscaped);

        $view->expects('escapeHtmlAttr')
            ->with($linkClass)
            ->andReturn($linkClassEscaped);

        $sut = new LinkNewWindow();
        $sut->setView($view);

        $this->assertSame($output, $sut->__invoke($url, $linkText, $linkClass, $hideNewTabMessage, $isExternal));
    }

    public static function dpIsExternalLink(): \Iterator
    {
        $internalLinkOutputHiddenNewTab = '<a href="http://url/escaped" class="escaped link class" target="_blank">escaped translated link text<span class="govuk-visually-hidden">escaped translated new tab text</span></a>';
        $externalLinkOutputHiddenNewTab = '<a href="http://url/escaped" class="escaped link class" target="_blank" rel="external noreferrer noopener">escaped translated link text<span class="govuk-visually-hidden">escaped translated new tab text</span></a>';
        $internalLinkOutputVisibleNewTab = '<a href="http://url/escaped" class="escaped link class" target="_blank">escaped translated link text escaped translated new tab text</a>';
        $externalLinkOutputVisibleNewTab = '<a href="http://url/escaped" class="escaped link class" target="_blank" rel="external noreferrer noopener">escaped translated link text escaped translated new tab text</a>';
        yield [true, false, $internalLinkOutputHiddenNewTab];
        yield [true, true, $externalLinkOutputHiddenNewTab];
        yield [false, false, $internalLinkOutputVisibleNewTab];
        yield [false, true, $externalLinkOutputVisibleNewTab];
    }
}
