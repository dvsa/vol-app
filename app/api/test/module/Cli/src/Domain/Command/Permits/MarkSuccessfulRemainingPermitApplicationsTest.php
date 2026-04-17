<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command\Permits;

use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulRemainingPermitApplications;

/**
 * Mark Successful Remaining Permit Applications test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MarkSuccessfulRemainingPermitApplicationsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $sut = MarkSuccessfulRemainingPermitApplications::create(
            [
                'stockId' => 7
            ]
        );

        static::assertEquals(7, $sut->getStockId());
    }
}
