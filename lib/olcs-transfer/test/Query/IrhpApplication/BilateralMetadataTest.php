<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralMetadata;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralMetadata
 */

class BilateralMetadataTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $irhpApplicationId = 30;

        $sut = BilateralMetadata::create(
            [
                'irhpApplication' => $irhpApplicationId,
            ]
        );
        $this->assertEquals($irhpApplicationId, $sut->getIrhpApplication());
        $this->assertEquals(
            [
                'irhpApplication' => $irhpApplicationId,
            ],
            $sut->getArrayCopy()
        );
    }
}
