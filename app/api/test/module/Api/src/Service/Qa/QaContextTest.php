<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see QaContext
 */
class QaContextTest extends MockeryTestCase
{
    private $applicationStep;

    private $qaEntity;

    private $qaContext;

    public function setUp(): void
    {
        $this->applicationStep = m::mock(ApplicationStep::class);

        $this->qaEntity = m::mock(QaEntityInterface::class);

        $this->qaContext = new QaContext($this->applicationStep, $this->qaEntity);
    }

    public function testGetApplicationStepEntity(): void
    {
        $this->assertSame(
            $this->applicationStep,
            $this->qaContext->getApplicationStepEntity()
        );
    }

    public function testGetQaEntity(): void
    {
        $this->assertSame(
            $this->qaEntity,
            $this->qaContext->getQaEntity()
        );
    }

    public function testGetAnswerValue(): void
    {
        $answerValue = 'foo';

        $this->qaEntity->shouldReceive('getAnswer')
            ->with($this->applicationStep)
            ->andReturn($answerValue);

        $this->assertEquals(
            $answerValue,
            $this->qaContext->getAnswerValue()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsApplicationStepEnabled')]
    public function testIsApplicationStepEnabled(
        mixed $isNotYetSubmitted,
        mixed $isUnderConsideration,
        mixed $enabledAfterSubmission,
        mixed $expected
    ): void {
        $this->qaEntity->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturn($isNotYetSubmitted);

        $this->qaEntity->shouldReceive('isUnderConsideration')
            ->withNoArgs()
            ->andReturn($isUnderConsideration);

        $this->applicationStep->shouldReceive('getEnabledAfterSubmission')
            ->withNoArgs()
            ->andReturn($enabledAfterSubmission);

        $this->assertEquals(
            $expected,
            $this->qaContext->isApplicationStepEnabled()
        );
    }

    public static function dpIsApplicationStepEnabled(): array
    {
        return [
            [true, true, true, true],
            [true, true, false, true],
            [true, false, true, true],
            [true, false, false, true],
            [false, true, true, true],
            [false, true, false, true],
            [false, false, true, true],
            [false, false, false, false],
        ];
    }
}
