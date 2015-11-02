<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\EbsrPack;
use Mockery as m;

/**
 * Class EbsrPackTest
 * @package OlcsTest\Service\Data
 */
class EbsrPackTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @param $data
     * @param $expected
     * @dataProvider provideFetchList
     */
    public function testFetchList($data, $expected)
    {
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('list/1', [])->andReturn($data);

        $sut = new EbsrPack();
        $sut->setRestClient($mockRestClient);

        $sut->fetchList();
        //ensure result is cached
        $result = $sut->fetchList();

        $this->assertEquals($expected, $result);
    }

    public function provideFetchList()
    {
        return [
            [false, false],
            [[], false],
            [['Results'=>[]], []],
            [['Results'=>[['result']]], [['result']]],
        ];
    }
}
