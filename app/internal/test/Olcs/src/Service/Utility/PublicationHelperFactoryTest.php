<?php

namespace OlcsTest\Service;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Utility\PublicationHelperFactory;
use Mockery as m;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PubicationHelperFactoryTest
 * @package OlcsTest\Service\Utility
 */
class PublicationHelperFactoryTest extends MockeryTestCase
{
    public function testCreateService()
    {
        $mockTrafficAreaService = m::mock('Generic\Service\Data\TrafficArea');
        $publicationLinkService = m::mock('Common\Service\Data\PublicationLink');

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Generic\Service\Data\TrafficArea')
            ->andReturn($mockTrafficAreaService);
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($publicationLinkService);

        $sut = new PublicationHelperFactory();
        $service = $sut->createService($mockServiceManager);

        $this->assertInstanceOf('Common\Service\Data\PublicationLink', $service->getPublicationLinkService());
        $this->assertInstanceOf('Generic\Service\Data\TrafficArea', $service->getTrafficAreaDataService());

    }
}
