<?php

declare(strict_types=1);

/**
 * Delete Condition/Undertakings Command Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\OperatingCentre;

use Dvsa\Olcs\Api\Domain\Command\OperatingCentre\DeleteConditionUndertakings;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Delete Condition/Undertakings Command Test
 *
  * @author Dan Eggleston <dan@stolenegg.com>
 */
class DeleteConditionUndertakingsTest extends MockeryTestCase
{
    public function testStructure(): void
    {
        $oc = m::mock(OperatingCentreEntity::class);
        $licence = m::mock(LicenceEntity::class);
        $application = m::mock(ApplicationEntity::class);

        $command = DeleteConditionUndertakings::create(
            [
                'operatingCentre' => $oc,
                'licence' => $licence,
                'application' => $application,
            ]
        );

        $this->assertSame($oc, $command->getOperatingCentre());
        $this->assertSame($licence, $command->getLicence());
        $this->assertSame($application, $command->getApplication());
    }
}
