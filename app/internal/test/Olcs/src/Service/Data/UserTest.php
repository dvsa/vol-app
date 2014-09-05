<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    private $users = [
        ['id' => 1, 'name' => 'Logged in user'],
        ['id' => 5, 'name' => 'Mr E'],
    ];

    public function testFetchPublicInquiryReasonData()
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

    public function testFetchPublicInquiryReasonDataFailure()
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
 