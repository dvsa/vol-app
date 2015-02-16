<?php
/**
 * Abstract Data Service Unit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace AdminTest\Service\Data;

use PHPUnit_Framework_TestCase;

/**
 * Abstract Data Service Unit Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

/**
 * Abstract Data Service Test
 * @package AdminTest\Service\Data
 */
abstract class AbstractDataServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock $service
     */
    public $service;

    /**
     * @var string $serviceName
     */
    public $serviceName = '\StdClass';

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

        $this->mockRestClient->expects($this->any())
            ->method('get')
            ->will($this->returnCallback([$this, 'mockRestCallGet']));

        $this->mockRestClient->expects($this->any())
            ->method('put')
            ->will($this->returnCallback([$this, 'mockRestCallPut']));

        $this->service->expects($this->any())
            ->method('getRestClient')
            ->will($this->returnValue($this->mockRestClient));
    }
}
