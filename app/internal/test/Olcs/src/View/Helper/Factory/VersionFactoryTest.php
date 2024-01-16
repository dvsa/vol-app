<?php

namespace OlcsTest\View\Helper;

use Interop\Container\ContainerInterface;
use Olcs\View\Helper\Version;
use Olcs\View\Helper\Factory\VersionFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OLCS\View\Helper\Factory\VersionFactory
 */
class VersionFactoryTest extends TestCase
{
    public function testFactoryWithNoVersionWillReturnNotSpecified()
    {
        $config = ['application_version' => ''];
        $serviceManager = $this->createMock(ContainerInterface::class);
        $serviceManager->method('get')->with($this->equalToIgnoringCase('Config'))->willReturn($config);

        $versionFactory = new VersionFactory();
        $version = ($versionFactory)($serviceManager, VersionFactory::class);

        $this->assertEquals('Not specified', $version->__invoke());
    }

    public function testFactoryWithInvalidVersionWillReturnNotSpecified()
    {
        $config = ['application_version' => ['number' => '1']];
        $serviceManager = $this->createMock(ContainerInterface::class);
        $serviceManager->method('get')->with($this->equalToIgnoringCase('Config'))->willReturn($config);

        $versionFactory = new VersionFactory();
        $version = ($versionFactory)($serviceManager, VersionFactory::class);

        $this->assertEquals('Not specified', $version->__invoke());
    }

    public function testFactoryWithVersionNumber()
    {
        $config['application_version'] = '1.123.111';
        $config = ['application_version' => '1.123.111'];
        $serviceManager = $this->createMock(ContainerInterface::class);
        $serviceManager->method('get')->with($this->equalToIgnoringCase('Config'))->willReturn($config);

        $versionFactory = new VersionFactory();
        $version = ($versionFactory)($serviceManager, VersionFactory::class);

        $this->assertEquals('1.123.111', $version->__invoke());
    }
}
