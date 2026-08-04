<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralMetadata;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralMetadata::class)]
final class BilateralMetadataTest extends \PHPUnit\Framework\TestCase
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
