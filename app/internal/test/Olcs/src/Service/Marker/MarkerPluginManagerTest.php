<?php

namespace OlcsTest\Service\Marker;

use Interop\Container\Containerinterface;
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
        $this->sut = new \Olcs\Service\Marker\MarkerPluginManager($this->createMock(Containerinterface::class));
        parent::setUp();
    }

    public function testConstructor()
    {
        $sut = new \Olcs\Service\Marker\MarkerPluginManager($this->createMock(Containerinterface::class));

        $this->assertInstanceOf(\Olcs\Service\Marker\MarkerPluginManager::class, $sut);
    }

    public function testValidate()
    {
        $mockPlugin = m::mock(MarkerInterface::class);

        $this->assertNull($this->sut->validate($mockPlugin));
    }

    public function testValidateInvalid()
    {
        $this->expectException(InvalidServiceException::class);

        $this->sut->validate(null);
    }

    /**
     * @todo To be removed as part of OLCS-28149
     */
    public function testValidatePlugin()
    {
        $mockPlugin = m::mock(MarkerInterface::class);

        $this->assertNull($this->sut->validate($mockPlugin));
    }

    /**
     * @todo To be removed as part of OLCS-28149
     */
    public function testValidatePluginInvalid()
    {
        $this->expectException(\RuntimeException::class);

        $this->sut->validate(null);
    }
}
