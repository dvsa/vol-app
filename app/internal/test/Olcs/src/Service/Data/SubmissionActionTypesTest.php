<?php

namespace OlcsTest\Service\Data;

use Common\Service\Data\RefData;
use CommonTest\Common\Service\Data\AbstractDataServiceTestCase;
use Olcs\Service\Data\SubmissionActionTypes;
use Mockery as m;

/**
 * Class SubmissionActionTypes Test
 * @package CommonTest\Service
 */
class SubmissionActionTypesTest extends AbstractDataServiceTestCase
{
    /** @var SubmissionActionTypes */
    protected $sut;

    /** @var  m\MockInterface */
    private $refDataService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refDataService = m::mock(RefData::class);

        $this->sut = new SubmissionActionTypes($this->abstractDataServiceServices, $this->refDataService);
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

        $this->refDataService->shouldReceive('fetchListData')
            ->with('sub_st_rec')
            ->once()
            ->andReturn($mockRefData);

        $result = $this->sut->fetchListOptions($context, $useGroups);

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

        $this->refDataService->shouldReceive('fetchListData')
            ->with('sub_st_rec')
            ->once()
            ->andReturn($mockRefData);

        $result = $this->sut->fetchListOptions($context, $useGroups);

        $this->assertArrayHasKey('option_id1', $result['sub_st_rec_group1']['options']);
    }

    public function testFetchListOptionsNoData()
    {
        $context = '';
        $useGroups = false;

        $mockRefData = [];

        $this->refDataService->shouldReceive('fetchListData')
            ->with('sub_st_rec')
            ->andReturn($mockRefData);

        $result = $this->sut->fetchListOptions($context, $useGroups);

        $this->assertEmpty($result);
    }
}
