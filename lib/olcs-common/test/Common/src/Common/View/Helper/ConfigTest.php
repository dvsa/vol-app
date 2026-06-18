<?php

namespace CommonTest\View\Helper;

use Common\View\Helper\Config;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\View\Helper\Config
 */
class ConfigTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $config = ['EXPECT'];
        $sut = new Config($config);

        static::assertEquals($config, $sut->__invoke());
    }
}
