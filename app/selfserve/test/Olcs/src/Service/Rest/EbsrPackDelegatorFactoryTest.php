<?php


namespace OlcsTest\Service\Rest;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Service\Rest\EbsrPackDelegatorFactory;
use Mockery as m;

/**
 * Class EbsrPackDelegatorFactoryTest
 * @package OlcsTest\Service\Rest
 */
class EbsrPackDelegatorFactoryTest extends TestCase
{
    public function testCreateDelegatorWithName()
    {
        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');

        $mockService = m::mock('Common\Util\RestClient');
        $mockService->shouldReceive('setResponseHelper')->with(m::type('Common\Util\MultiResponseHelper'));
        $callable = function () use ($mockService) {
            return $mockService;
        };

        $sut = new EbsrPackDelegatorFactory();
        $sut->createDelegatorWithName($mockSl, '', '', $callable);
    }
}
