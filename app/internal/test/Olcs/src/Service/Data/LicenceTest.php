<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Licence;

class LicenceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBundle()
    {
        $sut = new Licence();
        $this->assertInternalType('array', $sut->getBundle());
    }

    public function testSetId()
    {
        $sut = new Licence();
        $sut->setId(78);
        $this->assertEquals(78, $sut->getId());
    }

    public function testGetId()
    {
        $sut = new Licence();
        $this->assertNull($sut->getId());
    }

    public function testFetchLicenceData()
    {
        $licence = ['id' => 78, 'isNi' => true];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/78'), $this->isType('array'))
            ->willReturn($licence);

        $sut = new Licence();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($licence,$sut->fetchLicenceData(78));
        //test data is cached
        $this->assertEquals($licence,$sut->fetchLicenceData(78));
    }
}
 