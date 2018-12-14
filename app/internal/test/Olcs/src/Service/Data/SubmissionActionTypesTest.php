<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubmissionActionTypes;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * Class SubmissionActionTypes Test
 * @package CommonTest\Service
 */
class SubmissionActionTypesTest extends TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new SubmissionActionTypes();
    }

    public function testCreateService()
    {
        $mockRefDataService = $this->createMock('\Common\Service\Data\RefData');

        $mockSl = $this->createMock('\Zend\ServiceManager\ServiceManager');
        $mockSl->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['\Common\Service\Data\RefData', true, $mockRefDataService]
                ]
            );

        $service = $this->sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\SubmissionActionTypes', $service);
        $this->assertInstanceOf('\Common\Service\Data\RefData', $this->sut->getRefDataService());
    }

    public function testFetchListOptions()
    {
        $context = '';
        $useGroups = false;

        $mockRefData = [
            0 => [
                'id' => 'option_id1',
                'description' => 'Option 1',
                'parent' => [
                    'id' => 'sub_st_rec_group1',
                    'description' => 'Group 1'
                ]
            ]
        ];

        $mockRefDataService = m::mock('\Common\Service\Data\RefData');
        $mockRefDataService->shouldReceive('fetchListData')->with('sub_st_rec')
            ->andReturn($mockRefData);

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('\Common\Service\Data\RefData')->andReturn($mockRefDataService);

        $sut = $this->sut->createService($mockSl);

        $result = $sut->fetchListOptions($context, $useGroups);

        $this->assertArrayHasKey('option_id1', $result);
    }

    public function testFetchListOptionsAsGroups()
    {
        $context = '';
        $useGroups = true;

        $mockRefData = [
            0 => [
                'id' => 'option_id1',
                'description' => 'Option 1',
                'parent' => [
                    'id' => 'sub_st_rec_group1',
                    'description' => 'Group 1'
                ]
            ]
        ];

        $mockRefDataService = m::mock('\Common\Service\Data\RefData');
        $mockRefDataService->shouldReceive('fetchListData')->with('sub_st_rec')
            ->andReturn($mockRefData);

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('\Common\Service\Data\RefData')->andReturn($mockRefDataService);

        $sut = $this->sut->createService($mockSl);

        $result = $sut->fetchListOptions($context, $useGroups);

        $this->assertArrayHasKey('option_id1', $result['sub_st_rec_group1']['options']);
    }

    public function testFetchListOptionsNoData()
    {
        $context = '';
        $useGroups = false;

        $mockRefData = [];

        $mockRefDataService = m::mock('\Common\Service\Data\RefData');
        $mockRefDataService->shouldReceive('fetchListData')->with('sub_st_rec')
            ->andReturn($mockRefData);

        $mockSl = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockSl->shouldReceive('get')->with('\Common\Service\Data\RefData')->andReturn($mockRefDataService);

        $sut = $this->sut->createService($mockSl);

        $result = $sut->fetchListOptions($context, $useGroups);

        $this->assertEmpty($result);
    }
}
