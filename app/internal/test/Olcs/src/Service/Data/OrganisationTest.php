<?php
/**
 * Organisation Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Organisation;

/**
 * Organisation Service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OrganisationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mock $service
     */
    public $service;

    /**
     * @var string $serviceName
     */
     public $serviceName = '\Olcs\Service\Data\Organisation';

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

        $this->mockRestClient = $this->getMock('\StdClass', ['get', 'update', 'post']);

        $this->service->expects($this->any())
            ->method('getRestClient')
            ->will($this->returnValue($this->mockRestClient));
    }

    /**
     * Test get bundle method
     * @group organisationService
     */
    public function testGetBundle()
    {
        $bundle = $this->service->getBundle();
        $this->assertInternalType('array', $bundle);
        $this->assertEquals(count($bundle) > 0, true);
    }

    /**
     * Test get organisation method
     * @group organisationService
     */
    public function testGetOrganisation()
    {
        $organisation = ['key' => 'value'];
        $id = 1;

        $this->mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($organisation);

        $this->assertEquals($organisation, $this->service->getOrganisation($id, false));
        //test data is cached
        $this->assertEquals($organisation, $this->service->getOrganisation($id, true));

    }

    /**
     * Test update organisation method
     * @group organisationService
     */
    public function testUpdateOrganisation()
    {
        $organisation = ['key' => 'value', 'id' => 1];

        $this->mockRestClient->expects($this->once())
            ->method('update')
            ->with($this->equalTo('/1'), $this->isType('array'))
            ->willReturn(null);

        $this->assertEquals(null, $this->service->updateOrganisation($organisation));
    }

    /**
     * Test create organisation method
     * @group organisationService
     */
    public function testCreateOrganisation()
    {
        $organisation = ['key' => 'value'];

        $this->mockRestClient->expects($this->once())
            ->method('post')
            ->with($this->isType('array'))
            ->willReturn(['id' => 1]);

        $this->assertEquals(['id' => 1], $this->service->createOrganisation($organisation));
    }
}
