<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Team;

/**
 * Class TeamTest
 * @package OlcsTest\Service\Data
 */
class TeamTest extends \PHPUnit_Framework_TestCase
{
    private $teams = [
        ['id' => 1, 'name' => 'Development'],
        ['id' => 5, 'name' => 'Some other team'],
    ];

    public function setUp()
    {
        $this->markTestSkipped();
    }

    public function testFetchTeamData()
    {
        $teams = ['Results' =>
            $this->teams
        ];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($teams);

        $sut = new Team();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($this->teams, $sut->fetchTeamListData([]));
        // test data is cached - once() assertion, above is important
        $this->assertEquals($this->teams, $sut->fetchTeamListData([]));
    }

    public function testFetchTeamDataFailure()
    {
        $teams = [];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($teams);

        $sut = new Team();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals(false, $sut->fetchTeamListData([]));
        //test failure isn't retried
        $this->assertEquals(false, $sut->fetchTeamListData([]));
    }

    public function testFetchListOptions()
    {
        $sut = new Team();
        $sut->setData('teamlist', $this->teams);

        $this->assertEquals([1 => 'Development', 5 => 'Some other team'], $sut->fetchListOptions([]));
    }

    public function testFetchListOptionsEmpty()
    {
        $sut = new Team();
        $sut->setData('teamlist', false);

        $this->assertEquals([], $sut->fetchListOptions([]));
    }
}
