<?php

namespace OlcsTest\Service\Data\Search;

use Olcs\Data\Object\Search\Application;
use Olcs\Service\Data\Search\SearchTypeManagerFactory;
use Mockery as m;

/**
 * Class SearchTypeManagerFactoryTest
 * @package OlcsTest\Service\Data\Search
 */
class SearchTypeManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $search = new Application();

        $serviceConfig = ['search' => ['services' => ['testService' => $search]]];

        $sut = new SearchTypeManagerFactory();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Config')->andReturn($serviceConfig);

        $service = $sut->createService($mockSl);

        $this->assertInstanceOf('Olcs\Service\Data\Search\SearchTypeManager', $service);
        $this->assertEquals($search, $service->get('testService'));
    }
}
