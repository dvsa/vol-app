<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\User;

/**
 * Class UserTest
 * @package OlcsTest\Service\Data
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    private $users = [
        ['id' => 1, 'loginId' => 'Logged in user'],
        ['id' => 5, 'loginId' => 'Mr E'],
    ];

    public function testUserData()
    {
        $users = ['Results' =>
            $this->users
        ];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($users);

        $sut = new User();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($this->users, $sut->fetchUserListData([]));
        //test data is cached
        $this->assertEquals($this->users, $sut->fetchUserListData([]));
    }

    public function testUserDataForTeam()
    {
        $users = ['Results' =>
            $this->users
        ];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), ['bundle'=>json_encode([]), 'team'=>99])
            ->willReturn($users);

        $sut = new User();
        $sut->setTeam(99);
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($this->users, $sut->fetchUserListData([]));
    }

    public function testFetchUserDataFailure()
    {
        $users = [];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(''), $this->isType('array'))
            ->willReturn($users);

        $sut = new User();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals(false, $sut->fetchUserListData([]));
        //test failure isn't retried
        $this->assertEquals(false, $sut->fetchUserListData([]));
    }

    public function testFetchListOptions()
    {
        $sut = new User();
        $sut->setData('userlist', $this->users);

        $this->assertEquals([1 => 'Logged in user', 5 => 'Mr E'], $sut->fetchListOptions([]));
    }

    public function testFetchListOptionsEmpty()
    {
        $sut = new User();
        $sut->setData('userlist', false);

        $this->assertEquals([], $sut->fetchListOptions([]));
    }

    public function testGetBundle()
    {
        $sut = new User();
        $this->assertInternalType('array', $sut->getBundle());
    }
}
