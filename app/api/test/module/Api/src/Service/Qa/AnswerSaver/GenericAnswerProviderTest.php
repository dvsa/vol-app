<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\AnswerSaver;

use Dvsa\Olcs\Api\Domain\Repository\Answer as AnswerRepository;
use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerProvider;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GenericAnswerProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class GenericAnswerProviderTest extends MockeryTestCase
{
    public function testGet(): void
    {
        $questionId = 43;
        $qaEntityId = 124;
        $qaEntityCamelCaseEntityName = 'EntityName';

        $qaEntity = m::mock(QaEntityInterface::class);
        $qaEntity->shouldReceive('getId')
            ->andReturn($qaEntityId);
        $qaEntity->shouldReceive('getCamelCaseEntityName')
            ->andReturn($qaEntityCamelCaseEntityName);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);
        $qaContext->shouldReceive('getApplicationStepEntity->getQuestion->getId')
            ->withNoArgs()
            ->andReturn($questionId);

        $answer = m::mock(Answer::class);

        $answerRepo = m::mock(AnswerRepository::class);
        $answerRepo->shouldReceive('fetchByQuestionIdAndEntityTypeAndId')
            ->with($questionId, $qaEntityCamelCaseEntityName, $qaEntityId)
            ->andReturn($answer);

        $genericAnswerProvider = new GenericAnswerProvider($answerRepo);

        $this->assertSame(
            $answer,
            $genericAnswerProvider->get($qaContext)
        );
    }
}
