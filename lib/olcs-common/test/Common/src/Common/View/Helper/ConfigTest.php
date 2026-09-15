<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\View\Helper\Config;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\View\Helper\Config::class)]
final class ConfigTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $config = ['EXPECT'];
        $sut = new Config($config);

        $this->assertEquals($config, $sut->__invoke());
    }
}
