<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountries;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountry;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RestrictedCountriesTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedCountriesTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $yesNo = 'yesNo';
        $questionKey = 'question.key';

        $restrictedCountry1Representation = [
            'code' => 'GR',
            'labelTranslationKey' => 'Greece',
            'checked' => true
        ];

        $restrictedCountry1 = m::mock(RestrictedCountry::class);
        $restrictedCountry1->shouldReceive('getRepresentation')
            ->andReturn($restrictedCountry1Representation);

        $restrictedCountry2Representation = [
            'code' => 'RU',
            'labelTranslationKey' => 'Russia',
            'checked' => false
        ];

        $restrictedCountry2 = m::mock(RestrictedCountry::class);
        $restrictedCountry2->shouldReceive('getRepresentation')
            ->andReturn($restrictedCountry2Representation);

        $expectedRepresentation = [
            'yesNo' => $yesNo,
            'questionKey' => $questionKey,
            'countries' => [
                $restrictedCountry1Representation,
                $restrictedCountry2Representation
            ]
        ];

        $restrictedCountries = new RestrictedCountries($yesNo, $questionKey);
        $restrictedCountries->addRestrictedCountry($restrictedCountry1);
        $restrictedCountries->addRestrictedCountry($restrictedCountry2);

        $this->assertEquals(
            $expectedRepresentation,
            $restrictedCountries->getRepresentation()
        );
    }
}
