<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\CountryDeletingAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ThirdCountryAnswerSaver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ThirdCountryAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ThirdCountryAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $postData = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $qaContext = m::mock(QaContext::class);

        $countryDeletingAnswerSaver = m::mock(CountryDeletingAnswerSaver::class);
        $countryDeletingAnswerSaver->shouldReceive('save')
            ->with($qaContext, $postData, 'qanda.bilaterals.third-country.yes-answer')
            ->once();

        $thirdCountryAnswerSaver = new ThirdCountryAnswerSaver($countryDeletingAnswerSaver);
        $thirdCountryAnswerSaver->save($qaContext, $postData);
    }
}
