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

    public function testSendPackList()
    {
        $packs = ['abc123', 'efg456'];
        $data = ['abc123' => [], 'efg456' => ['Validation error']];

        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient
            ->shouldReceive('post')
            ->with('notify', ['organisationId' => 75, 'packs' => $packs])
            ->andReturn($data);

        $sut = new EbsrPack();
        $sut->setRestClient($mockRestClient);

        $result = $sut->sendPackList($packs);

        $this->assertEquals(['valid' => 1, 'errors' => 1, 'messages' => ['efg456' => ['Validation error']]], $result);
    }

    public function testSendPackListWithException()
    {
        $packs = ['abc123', 'efg456'];

        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient
            ->shouldReceive('post')
            ->with('notify', ['organisationId' => 75, 'packs' => $packs])
            ->andReturn(false);

        $sut = new EbsrPack();
        $sut->setRestClient($mockRestClient);
        $passed = false;

        try {
            $sut->sendPackList($packs);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() == 'Failed to submit packs for processing, please try again') {
                $passed = true;
            }
        }

        $this->assertTrue($passed, 'Expected exception no thrown');
    }
}
