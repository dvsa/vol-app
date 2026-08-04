<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\View\Factory\Helper;

use Dvsa\Olcs\Utils\View\Factory\Helper\GetPlaceholderFactory;
use Dvsa\Olcs\Utils\View\Helper\GetPlaceholder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Laminas\View\Helper\Placeholder;
use Laminas\View\HelperPluginManager;

final class GetPlaceholderFactoryTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new GetPlaceholderFactory();
    }

    public function testInvoke(): void
    {
        $viewHelperManager = $this->createStub(HelperPluginManager::class);
        $viewHelperManager->method('get')->willReturnMap([
            ['placeholder', $this->createStub(\Laminas\View\Helper\Placeholder::class)],
        ]);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            ['ViewHelperManager', $viewHelperManager],
        ]);

        $factory = $this->sut;
        $result = $factory($container, 'getPlaceholder');

        $this->assertInstanceOf(\Closure::class, $result);

        $getPlaceholder = $result('foo');
        $this->assertInstanceOf(GetPlaceholder::class, $getPlaceholder);
    }
}
