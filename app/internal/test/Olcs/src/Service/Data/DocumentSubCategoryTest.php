<?php

namespace OlcsTest\Service\Data;

use PHPUnit_Framework_TestCase as TestCase;
use Olcs\Service\Data\DocumentSubCategory;
use Mockery as m;

/**
 * Class DocumentSubCategoryTest
 * @package OlcsTest\Service\Data
 */
class DocumentSubCategoryTest extends TestCase
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
        $sut = new DocumentSubCategory();
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
            ->with('', ['isDoc' => true, 'category' => 'testing', 'sort' => 'subCategoryName'])
            ->andReturn($results);

        $sut = new DocumentSubCategory();
        $sut->setRestClient($mockRestClient);
        $sut->setCategory('testing');

        $this->assertEquals($results['Results'], $sut->fetchListData([]));
        $sut->fetchListData([]);
    }
}
