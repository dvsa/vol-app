<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\CountryDeletingAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\EmissionsStandardsAnswerSaver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsStandardsAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsStandardsAnswerSaverTest extends MockeryTestCase
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
            ->with($qaContext, $postData, 'qanda.bilaterals.emissions-standards.euro3-or-euro4')
            ->once();

        $emissionsStandardsAnswerSaver = new EmissionsStandardsAnswerSaver($countryDeletingAnswerSaver);
        $emissionsStandardsAnswerSaver->save($qaContext, $postData);
    }
}
