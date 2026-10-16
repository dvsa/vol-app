<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Generic;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath as Entity;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Mockery as m;

/**
 * ApplicationPath Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class ApplicationPathEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetAnswerValueByQuestionId(): void
    {
        $questionId = 42;
        $answerValue = 'answer value';

        $applicationStep = m::mock(ApplicationStep::class);

        $qaEntity = m::mock(QaEntityInterface::class);
        $qaEntity->shouldReceive('getAnswer')
            ->with($applicationStep)
            ->andReturn($answerValue);

        $applicationPath = m::mock(Entity::class)->makePartial();
        $applicationPath->shouldReceive('getApplicationStepByQuestionId')
            ->with($questionId)
            ->andReturn($applicationStep);

        $this->assertEquals(
            $answerValue,
            $applicationPath->getAnswerValueByQuestionId($questionId, $qaEntity)
        );
    }

    public function testGetAnswerValueByQuestionIdNull(): void
    {
        $questionId = 44;

        $qaEntity = m::mock(QaEntityInterface::class);

        $applicationPath = m::mock(Entity::class)->makePartial();
        $applicationPath->shouldReceive('getApplicationStepByQuestionId')
            ->with($questionId)
            ->andReturnNull();

        $this->assertNull(
            $applicationPath->getAnswerValueByQuestionId($questionId, $qaEntity)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetApplicationStepByQuestionId')]
    public function testGetApplicationStepByQuestionId(mixed $questionId, ?int $expectedApplicationStepIndex): void
    {
        $applicationSteps = [];
        foreach ([38, 40, 42] as $stepQuestionId) {
            $applicationStep = m::mock(ApplicationStep::class);
            $applicationStep->shouldReceive('getQuestion->getId')
                ->withNoArgs()
                ->andReturn($stepQuestionId);
            $applicationSteps[] = $applicationStep;
        }

        $applicationPath = new Entity();
        $applicationPath->setApplicationSteps(new ArrayCollection($applicationSteps));

        $this->assertSame(
            $expectedApplicationStepIndex === null ? null : $applicationSteps[$expectedApplicationStepIndex],
            $applicationPath->getApplicationStepByQuestionId($questionId)
        );
    }

    public static function dpGetApplicationStepByQuestionId(): array
    {
        return [
            [38, 0],
            [40, 1],
            [42, 2],
            [44, null],
        ];
    }
}
