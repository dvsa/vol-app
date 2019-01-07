<?php

namespace OlcsTest\Service\Marker;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * MarkerPluginManagerFactoryTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class MarkerPluginManagerFactoryTest extends TestCase
{
    /**
     * @var \Olcs\Service\Marker\MarkerPluginManager
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Marker\MarkerPluginManagerFactory();
        parent::setUp();
    }

    public function testCreateServiceEmptyConfig()
    {
        $mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);

        $mockSl->shouldReceive('get')->with('Config')->once()->andReturn(['marker_plugins' => []]);

        $this->assertInstanceOf(\Olcs\Service\Marker\MarkerPluginManager::class, $this->sut->createService($mockSl));
    }

    public function testCreateServiceWithConfig()
    {
        $mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);

        $mockSl->shouldReceive('get')->with('Config')->once()->andReturn(['marker_plugins' => ['XXX']]);

        $this->assertInstanceOf(\Olcs\Service\Marker\MarkerPluginManager::class, $this->sut->createService($mockSl));
    }
}
