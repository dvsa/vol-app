<?php

namespace OlcsTest\Service\Data;

use PHPUnit_Framework_TestCase as TestCase;
use Olcs\Service\Data\Cases;
use Mockery as m;
use Olcs\Data\Object\Cases as CaseDataObject;

/**
 * Class CasesTest
 * @package OlcsTest\Service\Data
 */
class CasesTest extends TestCase
{
    public function testFetchCaseData()
    {
        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('/33', m::type('array'))->andReturn(['id' => 33]);
        $sut = new Cases();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals(new CaseDataObject(['id' => 33]), $sut->fetchCaseData(33));
        $sut->fetchCaseData(33);
    }

    public function testGetBundle()
    {
        $sut = new Cases();
        $this->assertInternalType('array', $sut->getBundle());
    }

    public function testFetchData()
    {
        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('/33', m::type('array'))->andReturn(['id' => 33]);
        $sut = new Cases();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals(new CaseDataObject(['id' => 33]), $sut->fetchCaseData(33));
        $sut->fetchData(33);
    }

    public function testCanCloseAble()
    {
        $mockCase = [
            'id' => 33,
            'closedDate' => null,
            'outcomes' => [
                0 => [
                    'id' => 'case_o_curtail',
                    'description' => 'Curtail'
                ]
            ]
        ];
        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('/33', m::type('array'))->andReturn($mockCase);
        $sut = new Cases();
        $sut->setRestClient($mockRestClient);

        $this->assertTrue($sut->canClose(33));
    }

    public function testCanCloseAlreadyClosed()
    {
        $mockCase = [
            'id' => 33,
            'closedDate' => '2015-02-16',
            'outcome' => [
                0 => [
                    'id' => 'case_o_curtail',
                    'description' => 'Curtail'
                ]
            ]
        ];
        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('/33', m::type('array'))->andReturn($mockCase);
        $sut = new Cases();
        $sut->setRestClient($mockRestClient);

        $this->assertFalse($sut->canClose(33));
    }

    public function testCanCloseNoOutcome()
    {
        $mockCase = [
            'id' => 33,
            'closedDate' => null,
            'outcome' => null
        ];
        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('/33', m::type('array'))->andReturn($mockCase);
        $sut = new Cases();
        $sut->setRestClient($mockRestClient);

        $this->assertFalse($sut->canClose(33));
    }

    public function testCanReopen()
    {
        $mockCase = [
            'id' => 33,
            'closedDate' => '2015-02-16',
            'outcome' => [
                0 => [
                    'id' => 'case_o_curtail',
                    'description' => 'Curtail'
                ]
            ]
        ];
        $mockRestClient = m::mock('\Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->with('/33', m::type('array'))->andReturn($mockCase);
        $sut = new Cases();
        $sut->setRestClient($mockRestClient);

        $this->assertTrue($sut->canReopen(33));
    }
}
