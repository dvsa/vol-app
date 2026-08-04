<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary::class)]
final class SelfserveApplicationsSummaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = SelfserveApplicationsSummary::create(
            [
              'organisation' => 17,
            ]
        );
        $this->assertEquals(17, $sut->getOrganisation());
        $this->assertEquals(
            [
                'organisation' => 17,
            ],
            $sut->getArrayCopy()
        );
    }
}
