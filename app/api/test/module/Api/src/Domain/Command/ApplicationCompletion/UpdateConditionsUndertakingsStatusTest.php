<?php

/**
 * Update Conditions Undertakings Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\ApplicationCompletion;

use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateConditionsUndertakingsStatus;

/**
 * Update Conditions Undertakings Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateConditionsUndertakingsStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = UpdateConditionsUndertakingsStatus::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(['id' => 111], $command->getArrayCopy());
    }
}
