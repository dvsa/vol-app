<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\InternalApplicationsSummary;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpApplication\InternalApplicationsSummary::class)]
final class InternalApplicationsSummaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = InternalApplicationsSummary::create(
            [
                'licence' => 14,
            ]
        );
        $this->assertEquals(14, $sut->getLicence());
        $this->assertNull($sut->getStatus());
        $this->assertEquals(
            [
                'licence' => 14,
                'status' => null,
            ],
            $sut->getArrayCopy()
        );
    }

    public function testStructureOptional()
    {
        $sut = InternalApplicationsSummary::create(
            [
                'licence' => 14,
                'status' => 'app_status',
            ]
        );
        $this->assertEquals(14, $sut->getLicence());
        $this->assertEquals('app_status', $sut->getStatus());
        $this->assertEquals(
            [
                'licence' => 14,
                'status' => 'app_status',
            ],
            $sut->getArrayCopy()
        );
    }
}
