<?php

namespace Dvsa\OlcsTest\Utils\View\Factory\Helper;

use Dvsa\Olcs\Utils\View\Factory\Helper\AssetPathFactory;
use Dvsa\Olcs\Utils\View\Helper\AssetPath;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AssetPathFactoryTest extends TestCase
{
    public function test()
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('get')
            ->with('Config')
            ->willReturn(['unit_Config']);

        static::assertInstanceOf(
            AssetPath::class,
            (new AssetPathFactory())->__invoke($container, AssetPath::class)
        );
    }
}
