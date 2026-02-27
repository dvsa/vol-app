<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\BaseAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerSaver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GenericAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class GenericAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $postData = [
            'fieldset13' => [
                'qaElement' => 'qaElementValue'
            ]
        ];

        $qaContext = m::mock(QaContext::class);

        $baseAnswerSaver = m::mock(BaseAnswerSaver::class);
        $baseAnswerSaver->shouldReceive('save')
            ->with($qaContext, $postData)
            ->once();

        $genericAnswerSaver = new GenericAnswerSaver($baseAnswerSaver);
        $genericAnswerSaver->save($qaContext, $postData);
    }
}
