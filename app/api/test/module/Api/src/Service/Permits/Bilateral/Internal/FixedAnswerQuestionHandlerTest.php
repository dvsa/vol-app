<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\FixedAnswerQuestionHandler;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * FixedAnswerQuestionHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FixedAnswerQuestionHandlerTest extends MockeryTestCase
{
    public function testHandle(): void
    {
        $answer = 'some_answer';

        $requiredPermits = [
            'requiredPermitsKey1' => 'requiredPermitsValue1',
            'requiredPermitsKey2' => 'requiredPermitsValue2'
        ];

        $qaContext = m::mock(QaContext::class);

        $genericAnswerWriter = m::mock(GenericAnswerWriter::class);
        $genericAnswerWriter->shouldReceive('write')
            ->with($qaContext, $answer)
            ->once();

        $fixedAnswerQuestionHandler = new FixedAnswerQuestionHandler($genericAnswerWriter, $answer);

        $fixedAnswerQuestionHandler->handle($qaContext, $requiredPermits);
    }
}
