<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveIssuedPermitsSummary;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveIssuedPermitsSummary
 */

class SelfserveIssuedPermitsSummaryTest extends \PHPUnit\Framework\TestCase
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
