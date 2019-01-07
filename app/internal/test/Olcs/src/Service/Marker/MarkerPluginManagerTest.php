<?php

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

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

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Marker\MarkerPluginManager();
        parent::setUp();
    }

    public function testConstructor()
    {
        $mockConfig = m::mock(\Zend\ServiceManager\ConfigInterface::class);

        $mockConfig->shouldReceive('configureServiceManager')->twice();

        $sut = new \Olcs\Service\Marker\MarkerPluginManager($mockConfig);

        $this->assertInstanceOf(\Olcs\Service\Marker\MarkerPluginManager::class, $sut);
    }

    public function testValidatePluginTrue()
    {
        $mockPlugin = m::mock(\Olcs\Service\Marker\MarkerInterface::class);

        $this->assertTrue($this->sut->validatePlugin($mockPlugin));
    }

    public function testValidatePluginFalse()
    {
        $mockPlugin = m::mock();

        $this->expectException(\RuntimeException::class, 'Must implement MarkerInterface');

        $this->sut->validatePlugin($mockPlugin);
    }

    public function testInjectPartialHelper()
    {
        $mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockService = m::mock();

        $this->sut->setServiceLocator($mockSl);

        $mockSl->shouldReceive('get->get')->once()->andReturn('PARTIAL');
        $mockService->shouldReceive('setPartialHelper')->with('PARTIAL')->once();

        $this->sut->injectPartialHelper($mockService, $this->sut);
    }
}
