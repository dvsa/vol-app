<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\AnswersSummary;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpApplication\AnswersSummary
 */

class AnswersSummaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = AnswersSummary::create(
            [
                'id' => 1,
            ]
        );
        $this->assertEquals(1, $sut->getId());
        $this->assertNull($sut->getIrhpPermitApplication());
        $this->assertNull($sut->getTranslateToWelsh());
        $this->assertEquals(
            [
                'id' => 1,
                'irhpPermitApplication' => null,
                'translateToWelsh' => null,
            ],
            $sut->getArrayCopy()
        );
    }

    public function testStructureWithOptionalValues()
    {
        $sut = AnswersSummary::create(
            [
                'id' => 1,
                'irhpPermitApplication' => 100,
                'translateToWelsh' => 'Y',
            ]
        );
        $this->assertEquals(1, $sut->getId());
        $this->assertEquals(100, $sut->getIrhpPermitApplication());
        $this->assertEquals('Y', $sut->getTranslateToWelsh());
        $this->assertEquals(
            [
                'id' => 1,
                'irhpPermitApplication' => 100,
                'translateToWelsh' => 'Y',
            ],
            $sut->getArrayCopy()
        );
    }
}
