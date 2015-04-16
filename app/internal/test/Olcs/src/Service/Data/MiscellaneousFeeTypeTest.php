<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\MiscellaneousFeeType;

/**
 * Class MiscellaneousFeeTypeTest
 * @package OlcsTest\Service\Data
 */
class MiscellaneousFeeTypeTest extends \PHPUnit_Framework_TestCase
{
    private $types = [
        ['id' => 'MISC1', 'description' => 'Misc 1'],
        ['id' => 'MISC2', 'description' => 'Misc 2'],
    ];
    public function testFetchListData()
    {
        $types = [
            'Results' => $this->types,
        ];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo(['isMiscellaneous' => true]), $this->isType('array'))
            ->willReturn($types);

        $sut = new MiscellaneousFeeType();
        $sut->setRestClient($mockRestClient);

        $this->assertEquals($this->types, $sut->fetchListData([]));
        // test data is cached - once() assertion, above is important
        $this->assertEquals($this->types, $sut->fetchListData([]));
    }
}
