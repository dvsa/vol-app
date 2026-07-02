<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Laminas\View\HelperPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

class HelperPluginManagerAwareTraitTest extends MockeryTestCase
{
    public function testTrait(): void
    {
        $trait = $this->getMockForTrait(\Common\View\Helper\PluginManagerAwareTrait::class);

        $viewHelperManager = new HelperPluginManager(m::mock(ContainerInterface::class));

        $this->assertSame($viewHelperManager, $trait->setViewHelperManager($viewHelperManager)->getViewHelperManager());
    }
}
