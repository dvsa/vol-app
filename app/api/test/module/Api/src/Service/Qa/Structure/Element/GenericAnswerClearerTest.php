<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Answer as AnswerRepository;
use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerProvider;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerClearer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GenericAnswerClearerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class GenericAnswerClearerTest extends MockeryTestCase
{
    private $qaContext;

    private $genericAnswerProvider;

    private $answerRepo;

    private $genericAnswerClearer;

    public function setUp(): void
    {
        $this->qaContext = m::mock(QaContext::class);

        $this->genericAnswerProvider = m::mock(GenericAnswerProvider::class);

        $this->answerRepo = m::mock(AnswerRepository::class);

        $this->genericAnswerClearer = new GenericAnswerClearer(
            $this->genericAnswerProvider,
            $this->answerRepo
        );
    }

    public function testClear(): void
    {
        $answer = m::mock(Answer::class);

        $this->qaContext->shouldReceive('getQaEntity->getAnswers->remove')
            ->with($answer)
            ->once();

        $this->genericAnswerProvider->shouldReceive('get')
            ->with($this->qaContext)
            ->andReturn($answer);

        $this->answerRepo->shouldReceive('delete')
            ->with($answer)
            ->once();

        $this->genericAnswerClearer->clear($this->qaContext);
    }

    public function testClearDeleteNotRequired(): void
    {
        $this->genericAnswerProvider->shouldReceive('get')
            ->with($this->qaContext)
            ->andThrow(new NotFoundException());

        $this->answerRepo->shouldReceive('delete')
            ->never();

        $this->genericAnswerClearer->clear($this->qaContext);
    }
}
