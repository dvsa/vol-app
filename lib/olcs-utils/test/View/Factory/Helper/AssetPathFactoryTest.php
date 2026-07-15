<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\View\Factory\Helper;

use Dvsa\Olcs\Utils\View\Factory\Helper\AssetPathFactory;
use Dvsa\Olcs\Utils\View\Helper\AssetPath;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class AssetPathFactoryTest extends TestCase
{
    public function test()
    {
        $container = $this->createStub(ContainerInterface::class);

        $container
            ->method('get')
            ->willReturnMap([
                ['Config', ['unit_Config']],
            ]);

        $this->assertInstanceOf(AssetPath::class, new AssetPathFactory()->__invoke($container, AssetPath::class));
    }
}
