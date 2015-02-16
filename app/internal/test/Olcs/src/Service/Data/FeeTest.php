<?php
/**
 * Fee Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Fee;

/**
 * Fee Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mock $service
     */
    public $service;

    /**
     * @var string $serviceName
     */
     public $serviceName = '\Olcs\Service\Data\Fee';

    /**
     * @var \StdClass
     */
    public $mockRestClient;

    /**
     * @var array $mockMethods;
     */
    public $mockMethods = [];

    /**
     * Set up
     */
    protected function setUp()
    {
        $methods = array_merge($this->mockMethods, ['getRestClient']);

        $this->service = $this->getMock($this->serviceName, $methods);

        $this->mockRestClient = $this->getMock('\StdClass', ['get', 'put']);

        $this->service->expects($this->any())
            ->method('getRestClient')
            ->will($this->returnValue($this->mockRestClient));
    }

    /**
     * Test get bundle method
     * @group feeService
     */
    public function testGetBundle()
    {
        $bundle = $this->service->getBundle();
        $this->assertInternalType('array', $bundle);
        $this->assertEquals(count($bundle) > 0, true);
    }

    /**
     * Test get fees method
     * @group feeService
     */
    public function testGetFees()
    {
        $fees = ['key' => 'value'];
        $someParams = ['param' => 'value'];

        $this->mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($fees);

        $this->assertEquals($fees, $this->service->getFees($someParams));
        //test data is cached
        $this->assertEquals($fees, $this->service->getFees($someParams));

    }

    /**
     * Test get fees method
     * @group feeService
     */
    public function testGetFee()
    {
        $fee = ['key' => 'value'];
        $id = 1;

        $this->mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($fee);

        $this->assertEquals($fee, $this->service->getFee($id));
        //test data is cached
        $this->assertEquals($fee, $this->service->getFee($id));
    }
}
