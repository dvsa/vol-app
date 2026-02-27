<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\View\Helper;

use Dvsa\Olcs\Email\View\Helper\EmailStyle;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class EmailStyleTest
 */
class EmailStyleTest extends MockeryTestCase
{
    public function testSendSuccess(): void
    {
        $sut = new EmailStyle();
        $result = $sut->primaryButton();

        $this->assertSame(
            'background-color: #00823b; color: #fff; border-color: #004f24; display: inline-block; ' .
            'vertical-align: top; font-size: 1rem; padding: 0.5em 0.75em; margin-right: 0.3em; text-decoration: none; ' .
            'text-rendering: optimizeLegibility; cursor: pointer; border-bottom: 2px solid; padding-bottom: 0.4em; ' .
            'line-height: 1.4;',
            $result
        );
    }
}
