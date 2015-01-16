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
}
