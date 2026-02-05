<?php

declare(strict_types=1);

namespace OlcsTest\Service\Marker;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\RuntimeException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Service\Marker\MarkerInterface;

/**
 * MarkerPluginManagerTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkerPluginManagerTest extends TestCase
{
    /**
     * @var \Olcs\Service\Marker\MarkerPluginManager
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Service\Marker\MarkerPluginManager($this->createStub(ContainerInterface::class));
        parent::setUp();
    }

    public function testConstructor(): void
    {
        $sut = new \Olcs\Service\Marker\MarkerPluginManager($this->createStub(ContainerInterface::class));

        $this->assertInstanceOf(\Olcs\Service\Marker\MarkerPluginManager::class, $sut);
    }

    public function testValidate(): void
    {
        $mockPlugin = m::mock(MarkerInterface::class);

        $this->assertNull($this->sut->validate($mockPlugin));
    }

    public function testValidateInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);

        $this->sut->validate(null);
    }

    /**
     * @todo To be removed as part of OLCS-28149
     */
    public function testValidatePlugin(): void
    {
        $mockPlugin = m::mock(MarkerInterface::class);

        $this->assertNull($this->sut->validate($mockPlugin));
    }

    /**
     * @todo To be removed as part of OLCS-28149
     */
    public function testValidatePluginInvalid(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->sut->validate(null);
    }
}
