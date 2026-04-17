<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsText;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsTextTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsTextTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $name = 'noOfPermitsName';
        $label = 'noOfPermitsLabel';
        $hint = 'noOfPermitsHint';
        $value = 'noOfPermitsValue';

        $noOfPermitsText = new NoOfPermitsText($name, $label, $hint, $value);

        $expectedRepresentation = [
            'name' => $name,
            'label' => $label,
            'hint' => $hint,
            'value' => $value,
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $noOfPermitsText->getRepresentation()
        );
    }
}
