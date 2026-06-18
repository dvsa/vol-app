<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpApplication\SelfserveApplicationsSummary
 */

class SelfserveApplicationsSummaryTest extends \PHPUnit\Framework\TestCase
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
