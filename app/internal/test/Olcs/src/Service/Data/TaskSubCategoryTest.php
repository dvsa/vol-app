<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\TaskSubCategory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class DocumentSubCategoryTest
 * @package OlcsTest\Service\Data
 */
class TaskSubCategoryTest extends MockeryTestCase
{
    public function testFetchListData()
    {
        $results = [
            'Results' => [
                ['result1'],
                ['result2']
            ]
        ];

        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('', m::type('array'))->andReturn($results);
        $sut = new TaskSubCategory();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($results['Results'], $sut->fetchListData([]));
        $sut->fetchListData([]);
    }

    public function testFetchListDataWithCategory()
    {
        $results = [
            'Results' => [
                ['result1'],
                ['result2']
            ]
        ];

        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')
            ->once()
            ->with('', ['isTask' => true, 'category' => 'testing', 'sort' => 'subCategoryName'])
            ->andReturn($results);

        $sut = new TaskSubCategory();
        $sut->setRestClient($mockRestClient);
        $sut->setCategory('testing');

        $this->assertEquals($results['Results'], $sut->fetchListData([]));
        $sut->fetchListData([]);
    }
}
