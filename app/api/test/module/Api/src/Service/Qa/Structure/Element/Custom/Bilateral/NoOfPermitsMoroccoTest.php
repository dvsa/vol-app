<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMorocco;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsMoroccoTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsMoroccoTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $label = 'textbox.label.key';
        $value = '47';

        $noOfPermitsMorocco = new NoOfPermitsMorocco($label, $value);

        $expectedRepresentation = [
            'label' => $label,
            'value' => $value
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $noOfPermitsMorocco->getRepresentation()
        );
    }
}
