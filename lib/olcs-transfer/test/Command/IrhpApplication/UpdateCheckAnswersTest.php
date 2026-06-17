<?php

namespace Dvsa\OlcsTest\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCheckAnswers;

/**
 * UpdateCheckAnswers test
 */
class UpdateCheckAnswersTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = UpdateCheckAnswers::create(
            [
                'id' => 1,
            ]
        );

        $this->assertEquals(1, $sut->getId());
        $this->assertNull($sut->getIrhpPermitApplication());
        $this->assertEquals(
            [
                'id' => 1,
                'irhpPermitApplication' => null,
            ],
            $sut->getArrayCopy()
        );
    }

    public function testStructureWithOptionalValues()
    {
        $sut = UpdateCheckAnswers::create(
            [
                'id' => 1,
                'irhpPermitApplication' => 100,
            ]
        );
        $this->assertEquals(1, $sut->getId());
        $this->assertEquals(100, $sut->getIrhpPermitApplication());
        $this->assertEquals(
            [
                'id' => 1,
                'irhpPermitApplication' => 100,
            ],
            $sut->getArrayCopy()
        );
    }
}
