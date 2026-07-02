<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\EscapeHtml;
use HTMLPurifier;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * Test EscapeHtml view helper
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class EscapeHtmlTest extends TestCase
{
    /**
     * Test Escape HTML helper
     */
    public function testEscapeHtml(): void
    {
        $mockHtmlPurifier = m::mock(HtmlPurifier::class);
        $mockHtmlPurifier->shouldReceive('purify')
            ->once()
            ->with('<badtag>foo</badtag>')
            ->andReturn('foo');
        $viewHelper = new EscapeHtml($mockHtmlPurifier);
        $this->assertEquals('foo', $viewHelper->__invoke('<badtag>foo</badtag>'));
    }

    public function testInvokeReturnsAnEmptyStringWhenPassedNull(): void
    {
        $htmlPurifier = new HtmlPurifier();
        $viewHelper = new EscapeHtml($htmlPurifier);
        $this->assertEquals('', $viewHelper->__invoke(null));
    }

    public function testInvokeReturnsANumericStringWhenANumericString(): void
    {
        $htmlPurifier = new HtmlPurifier();
        $viewHelper = new EscapeHtml($htmlPurifier);
        $this->assertEquals('123', $viewHelper->__invoke('123'));
    }
}
