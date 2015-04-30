<?php


namespace OlcsTest\Service\Data;

use Common\Util\RestClient;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Service\Data\RequestMap;

/**
 * Class RequestMapTest
 * @package OlcsTest\Service\Data
 */
class RequestMapTest extends TestCase
{
    public function testGetServiceName()
    {
        $sut = new RequestMap();
        $this->assertInternalType('string', $sut->getServiceName());
    }

    public function testRequestMap()
    {
        $expected = ['busRegId' => 18, 'scale' => 'small'];

        $mockRestClient = m::mock(RestClient::class);
        $mockRestClient->shouldReceive('post')->with('', $expected)->andReturn('response');

        $sut = new RequestMap();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals('response', $sut->requestMap(18, 'small'));
    }
}
