<?php

namespace Dvsa\OlcsTest\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulSectorPermitApplications;

/**
 * Mark Successful Sector Permit Applications test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MarkSuccessfulSectorPermitApplicationsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = MarkSuccessfulSectorPermitApplications::create(
            [
                'stockId' => 7
            ]
        );

        static::assertEquals(7, $sut->getStockId());
    }
}
