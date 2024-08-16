<?php

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
class ApplicationPathEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetAnswerValueByQuestionId()
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

    public function testGetAnswerValueByQuestionIdNull()
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

    /**
     * @dataProvider dpGetApplicationStepByQuestionId
     */
    public function testGetApplicationStepByQuestionId($applicationSteps, $questionId, $expectedApplicationStep)
    {
        $applicationPath = new Entity();
        $applicationPath->setApplicationSteps($applicationSteps);

        $this->assertSame(
            $expectedApplicationStep,
            $applicationPath->getApplicationStepByQuestionId($questionId)
        );
    }

    public function dpGetApplicationStepByQuestionId()
    {
        $applicationStep1 = m::mock(ApplicationStep::class);
        $applicationStep1->shouldReceive('getQuestion->getId')
            ->withNoArgs()
            ->andReturn(38);

        $applicationStep2 = m::mock(ApplicationStep::class);
        $applicationStep2->shouldReceive('getQuestion->getId')
            ->withNoArgs()
            ->andReturn(40);

        $applicationStep3 = m::mock(ApplicationStep::class);
        $applicationStep3->shouldReceive('getQuestion->getId')
            ->withNoArgs()
            ->andReturn(42);

        $applicationSteps = new ArrayCollection([$applicationStep1, $applicationStep2, $applicationStep3]);

        return [
            [$applicationSteps, 38, $applicationStep1],
            [$applicationSteps, 40, $applicationStep2],
            [$applicationSteps, 42, $applicationStep3],
            [$applicationSteps, 44, null],
        ];
    }
}
