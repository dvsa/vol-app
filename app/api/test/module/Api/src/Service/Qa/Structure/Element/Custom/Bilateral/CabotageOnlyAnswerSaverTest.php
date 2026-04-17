<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\CabotageOnlyAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\CountryDeletingAnswerSaver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CabotageOnlyAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CabotageOnlyAnswerSaverTest extends MockeryTestCase
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
            ->with($qaContext, $postData, Answer::BILATERAL_CABOTAGE_ONLY)
            ->once();

        $cabotageOnlyAnswerSaver = new CabotageOnlyAnswerSaver($countryDeletingAnswerSaver);
        $cabotageOnlyAnswerSaver->save($qaContext, $postData);
    }
}
