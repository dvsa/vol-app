<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountry;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RestrictedCountryTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedCountryTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $code = 'GR';
        $labelTranslationKey = 'Greece';
        $checked = true;

        $expectedRepresentation = [
            'code' => $code,
            'labelTranslationKey' => $labelTranslationKey,
            'checked' => $checked
        ];

        $restrictedCountry = new RestrictedCountry($code, $labelTranslationKey, $checked);

        $this->assertEquals(
            $expectedRepresentation,
            $restrictedCountry->getRepresentation()
        );
    }
}
