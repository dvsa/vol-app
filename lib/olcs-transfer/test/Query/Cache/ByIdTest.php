<?php

namespace Dvsa\OlcsTest\Transfer\Query\Cache;

use Dvsa\Olcs\Transfer\Query\Cache\ById;

class ByIdTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = ById::create(
            [
                'id' => 'test',
                'uniqueId' => 'test2',
                'shouldRegen' => true,
            ]
        );

        $this->assertEquals('test', $sut->getId());
        $this->assertEquals('test2', $sut->getUniqueId());
        $this->assertTrue($sut->getShouldRegen());

        $this->assertEquals(
            [
                'id' => 'test',
                'uniqueId' => 'test2',
                'shouldRegen' => true,
            ],
            $sut->getArrayCopy()
        );
    }
}
