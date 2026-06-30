<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\LinkNewWindowExternal;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see LinkNewWindowExternal
 */
class LinkNewWindowExternalTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $linkText = 'link text';
        $hideNewTabMessage = false;
        $linkClass = 'link class';
        $url = 'http://url';
        $output = 'output';

        $view = m::mock(RendererInterface::class);
        $view->expects('linkNewWindow')
            ->with($url, $linkText, $linkClass, $hideNewTabMessage, true)
            ->andReturn($output);

        $sut = new LinkNewWindowExternal();
        $sut->setView($view);

        self::assertEquals($output, $sut->__invoke($url, $linkText, $linkClass, $hideNewTabMessage));
    }
}
