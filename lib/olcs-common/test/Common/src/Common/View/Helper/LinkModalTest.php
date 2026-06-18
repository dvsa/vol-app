<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\LinkModal;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see LinkModal
 */
class LinkModalTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $linkText = 'link text';
        $linkClass = 'link class';
        $url = 'http://url';
        $output = 'output';
        $adjustedLinkClass = $linkClass . ' ' . LinkModal::EXTRA_MODAL_CLASS;

        $view = m::mock(RendererInterface::class);
        $view->expects('link')->with($url, $linkText, $adjustedLinkClass)->andReturn($output);

        $sut = new LinkModal();
        $sut->setView($view);

        self::assertEquals($output, $sut->__invoke($url, $linkText, $linkClass));
    }
}
