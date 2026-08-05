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
final class QaContextTest extends MockeryTestCase
{
    private $applicationStep;

    private $qaEntity;

    private $qaContext;

    #[\Override]
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

    public static function dpIsApplicationStepEnabled(): \Iterator
    {
        yield [true, true, true, true];
        yield [true, true, false, true];
        yield [true, false, true, true];
        yield [true, false, false, true];
        yield [false, true, true, true];
        yield [false, true, false, true];
        yield [false, false, true, true];
        yield [false, false, false, false];
    }
}
