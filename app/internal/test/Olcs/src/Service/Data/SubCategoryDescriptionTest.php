<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubCategoryDescription;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SubCategoryDescriptionTest
 * @package OlcsTest\Service\Data
 */
class SubCategoryDescriptionTest extends MockeryTestCase
{
    public function setUp()
    {
        $this->markTestSkipped();
    }

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
        $sut = new SubCategoryDescription();
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
            ->with('', ['subCategory' => 'testing'])
            ->andReturn($results);

        $sut = new SubCategoryDescription();
        $sut->setRestClient($mockRestClient);
        $sut->setSubCategory('testing');

        $this->assertEquals($results['Results'], $sut->fetchListData([]));
        $sut->fetchListData([]);
    }

    public function testGetDescriptionFromId()
    {
        $sut = new SubCategoryDescription();
        $sut->setData('all', [['description' => 'test', 'id' => 4]]);

        $this->assertEquals('test', $sut->getDescriptionFromId(4));
        $this->assertNull($sut->getDescriptionFromId(123));
    }
}
