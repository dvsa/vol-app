<?php

namespace Dvsa\OlcsTest\Utils\View\Factory\Helper;

use Dvsa\Olcs\Utils\View\Factory\Helper\GetPlaceholderFactory;
use Dvsa\Olcs\Utils\View\Helper\GetPlaceholder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Laminas\View\Helper\Placeholder;
use Laminas\View\HelperPluginManager;

class GetPlaceholderFactoryTest extends TestCase
{
    protected $sut;

    /**
     * @var Placeholder|MockObject
     */
    protected $mockPlaceholder;

    public function setUp(): void
    {
        $this->sut = new GetPlaceholderFactory();
        $this->mockPlaceholder = $this->createMock(Placeholder::class);
    }

    public function testInvoke(): void
    {
        $viewHelperManager = $this->createMock(HelperPluginManager::class);
        $viewHelperManager->method('get')->with('placeholder')->willReturn($this->mockPlaceholder);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with('ViewHelperManager')->willReturn($viewHelperManager);

        $factory = $this->sut;
        $result = $factory($container, 'getPlaceholder');

        $this->assertInstanceOf(\Closure::class, $result);

        $getPlaceholder = $result('foo');
        $this->assertInstanceOf(GetPlaceholder::class, $getPlaceholder);
    }
}
