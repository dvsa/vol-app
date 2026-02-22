<?php

declare(strict_types=1);

/**
 * Delete Application Links Command Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\OperatingCentre;

use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteApplicationLinks;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Delete Application Links Command Test
 *
  * @author Dan Eggleston <dan@stolenegg.com>
 */
class DeleteApplicationLinksTest extends MockeryTestCase
{
    public function testStructure(): void
    {
        $oc = m::mock(OperatingCentreEntity::class);

        $command = DeleteApplicationLinks::create(
            [
                'operatingCentre' => $oc,
            ]
        );

        $this->assertSame($oc, $command->getOperatingCentre());
    }
}
