<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\EmissionsCategory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsCategoryTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsCategoryTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $type = 'euro5';
        $value = '45';
        $permitsRemaining = '62';

        $expectedRepresentation = [
            'type' => $type,
            'value' => $value,
            'permitsRemaining' => $permitsRemaining
        ];

        $emissionsCategory = new EmissionsCategory($type, $value, $permitsRemaining);

        $this->assertSame(
            $expectedRepresentation,
            $emissionsCategory->getRepresentation()
        );
    }
}
