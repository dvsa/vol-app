<?php

declare(strict_types=1);

/**
 * PlaceholderFactory Test
 */

namespace OlcsTest\Mvc\Controller\Plugin;

use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\Controller\Plugin\Placeholder;
use Olcs\Mvc\Controller\Plugin\PlaceholderFactory;
use Laminas\View\Helper\Placeholder as ViewPlaceholder;

/**
 * PlaceholderFactory Test
 */
class PlaceholderFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $viewPlaceholder = new ViewPlaceholder();

        $mockSl = m::mock(ContainerInterface::class);

        $mockSl->shouldReceive('get')->with('ViewHelperManager')->once()->andReturnSelf();

        $mockSl->shouldReceive('get')->with('placeholder')->once()->andReturn($viewPlaceholder);

        $sut = new PlaceholderFactory();
        $obj = $sut->__invoke($mockSl, Placeholder::class);

        $this->assertInstanceOf(Placeholder::class, $obj);
    }
}
