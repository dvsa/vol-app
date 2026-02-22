<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\Generic\Question as QuestionEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\BaseAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\AnnualTripsAbroadAnswerSaver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnnualTripsAbroadAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnnualTripsAbroadAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $postData = [
            'fieldset68' => [
                'qaElement' => '14'
            ]
        ];

        $qaContext = m::mock(QaContext::class);

        $baseAnswerSaver = m::mock(BaseAnswerSaver::class);
        $baseAnswerSaver->shouldReceive('save')
            ->with($qaContext, $postData, QuestionEntity::QUESTION_TYPE_STRING)
            ->once();

        $annualTripsAbroadAnswerSaver = new AnnualTripsAbroadAnswerSaver($baseAnswerSaver);
        $annualTripsAbroadAnswerSaver->save($qaContext, $postData);
    }
}
