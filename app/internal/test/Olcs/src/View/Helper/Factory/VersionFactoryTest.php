<?php

namespace OlcsTest\View\Helper;

use Mockery as m;
use OlcsTest\Bootstrap;
use PHPUnit_Framework_TestCase;
use Olcs\View\Helper\Factory\VersionFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class VersionFactoryTest
 * @package OlcsTest\View\Helper\Factory
 * @covers \OLCS\View\Helper\Factory\VersionFactory
 */
class VersionFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * @var array
     */
    protected $config = [];

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getRealServiceManager();
        $this->config = $this->serviceManager->get('config');
    }

    private function overrideConfig(array $config = [])
    {
        $this->serviceManager->setService('config', $config);
    }

    /**
     * @return m\MockInterface|ServiceLocatorInterface
     */
    private function getMockViewServiceLocator()
    {
        $serviceLocator = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $serviceLocator->shouldReceive('getServiceLocator->get')
            ->with('config')
            ->once()
            ->andReturn($this->config);

        return $serviceLocator;
    }

    public function testFactoryWithNoVersionWillReturnNotSpecified()
    {
        $this->config['application_version'] = '';
        $this->overrideConfig($this->config);

        $versionFactory = new VersionFactory();
        $version = $versionFactory->createService(
            $this->getMockViewServiceLocator()
        );

        $this->assertEquals('Not specified', $version->__invoke());
    }

    public function testFactoryWithInvalidVersionWillReturnNotSpecified()
    {
        $this->config['application_version'] = ['number' => '1'];
        $this->overrideConfig($this->config);

        $versionFactory = new VersionFactory();
        $version = $versionFactory->createService(
            $this->getMockViewServiceLocator()
        );

        $this->assertEquals('Not specified', $version->__invoke());
    }

    public function testFactoryWithVersionNumber()
    {
        $this->config['application_version'] = '1.123.111';
        $this->overrideConfig($this->config);

        $versionFactory = new VersionFactory();
        $version = $versionFactory->createService(
            $this->getMockViewServiceLocator()
        );

        $this->assertEquals('1.123.111', $version->__invoke());
    }
}
