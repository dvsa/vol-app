<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveIssuedPermitsSummary;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveIssuedPermitsSummary::class)]
final class SelfserveIssuedPermitsSummaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = SelfserveIssuedPermitsSummary::create(
            [
              'organisation' => 18,
            ]
        );
        $this->assertEquals(18, $sut->getOrganisation());
        $this->assertEquals(
            [
                'organisation' => 18,
            ],
            $sut->getArrayCopy()
        );
    }
}
