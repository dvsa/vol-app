<?php

namespace OlcsTest\Service\Data;

use PHPUnit_Framework_TestCase as TestCase;
use Olcs\Service\Data\Category;
use Mockery as m;

/**
 * Class CategoryTest
 * @package OlcsTest\Service\Data
 */
class CategoryTest extends TestCase
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
        $sut = new Category();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($results['Results'], $sut->fetchListData([]));
        $sut->fetchListData([]);
    }

    public function testGetIdFromHandle()
    {
        $sut = new Category();
        $sut->setData('categories', [['handle' => 'test', 'id' => 4]]);

        $this->assertEquals(4, $sut->getIdFromHandle('test'));
        $this->assertNull($sut->getIdFromHandle('non-existant'));
    }

    public function testGetDescriptionFromId()
    {
        $sut = new Category();
        $sut->setData('categories', [['description' => 'test', 'id' => 4]]);

        $this->assertEquals('test', $sut->getDescriptionFromId(4));
        $this->assertNull($sut->getDescriptionFromId(123));
    }
}
