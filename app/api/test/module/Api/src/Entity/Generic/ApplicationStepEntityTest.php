<?php

namespace Dvsa\OlcsTest\Api\Entity\Generic;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as Entity;
use Mockery as m;

/**
 * ApplicationStep Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ApplicationStepEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetNextStepSlug()
    {
        $nextStepSlug = 'number-of-permits';

        $previousApplicationStep = m::mock(Entity::class)->makePartial();

        $currentApplicationStep = m::mock(Entity::class)->makePartial();

        $nextApplicationStep = m::mock(Entity::class)->makePartial();
        $nextApplicationStep->shouldReceive('getQuestion->getSlug')
            ->andReturn($nextStepSlug);

        $applicationStepsValues = [
            $previousApplicationStep,
            $currentApplicationStep,
            $nextApplicationStep
        ];

        $currentApplicationStep->shouldReceive('getApplicationPath->getApplicationSteps->getValues')
            ->andReturn($applicationStepsValues);

        $this->assertEquals(
            $nextStepSlug,
            $currentApplicationStep->getNextStepSlug()
        );
    }

    public function testGetNextStepSlugCheckAnswers()
    {
        $previousApplicationStep = m::mock(Entity::class)->makePartial();

        $currentApplicationStep = m::mock(Entity::class)->makePartial();

        $applicationStepsValues = [
            $previousApplicationStep,
            $currentApplicationStep
        ];

        $currentApplicationStep->shouldReceive('getApplicationPath->getApplicationSteps->getValues')
            ->andReturn($applicationStepsValues);

        $this->assertEquals(
            'check-answers',
            $currentApplicationStep->getNextStepSlug()
        );
    }

    public function testGetPreviousStepSlug()
    {
        $previousStepSlug = 'previous-slug';

        $previousApplicationStep = m::mock(Entity::class);
        $previousApplicationStep->shouldReceive('getQuestion->getSlug')
            ->withNoArgs()
            ->andReturn($previousStepSlug);

        $currentApplicationStep = m::mock(Entity::class)->makePartial();

        $currentApplicationStep->shouldReceive('getPreviousApplicationStep')
            ->withNoArgs()
            ->andReturn($previousApplicationStep);

        $this->assertEquals(
            $previousStepSlug,
            $currentApplicationStep->getPreviousStepSlug()
        );
    }

    public function testGetPreviousStepSlugNull()
    {
        $currentApplicationStep = m::mock(Entity::class)->makePartial();

        $currentApplicationStep->shouldReceive('getPreviousApplicationStep')
            ->withNoArgs()
            ->andThrow(new NotFoundException());

        $this->assertNull(
            $currentApplicationStep->getPreviousStepSlug()
        );
    }

    public function testGetPreviousApplicationStep()
    {
        $previousApplicationStep = m::mock(Entity::class)->makePartial();

        $currentApplicationStep = m::mock(Entity::class)->makePartial();

        $nextApplicationStep = m::mock(Entity::class)->makePartial();

        $applicationStepsValues = [
            $previousApplicationStep,
            $currentApplicationStep,
            $nextApplicationStep
        ];

        $currentApplicationStep->shouldReceive('getApplicationPath->getApplicationSteps->getValues')
            ->andReturn($applicationStepsValues);

        $this->assertSame(
            $previousApplicationStep,
            $currentApplicationStep->getPreviousApplicationStep()
        );
    }

    public function testGetPreviousApplicationStepNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No previous application step found');

        $currentApplicationStep = m::mock(Entity::class)->makePartial();

        $nextApplicationStep = m::mock(Entity::class)->makePartial();

        $applicationStepsValues = [
            $currentApplicationStep,
            $nextApplicationStep
        ];

        $currentApplicationStep->shouldReceive('getApplicationPath->getApplicationSteps->getValues')
            ->andReturn($applicationStepsValues);

        $currentApplicationStep->getPreviousApplicationStep();
    }

    public function testGetFieldsetName()
    {
        $applicationStep = m::mock(Entity::class)->makePartial();
        $applicationStep->setId(345);

        $this->assertEquals(
            'fieldset345',
            $applicationStep->getFieldsetName()
        );
    }

    public function testGetDecodedOptionSource()
    {
        $decodedOptionSource = [
            'option1' => 'value1',
            'option2' => 'value2'
        ];

        $applicationStep = m::mock(Entity::class)->makePartial();
        $applicationStep->shouldReceive('getQuestion->getDecodedOptionSource')
            ->andReturn($decodedOptionSource);

        $this->assertEquals(
            $decodedOptionSource,
            $applicationStep->getDecodedOptionSource()
        );
    }
}
