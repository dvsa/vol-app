<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\QuestionHandlerDelegator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\QuestionHandlerInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * QuestionHandlerDelegatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QuestionHandlerDelegatorTest extends MockeryTestCase
{
    public const HANDLER_2_QUESTION_ID = 60;

    public const REQUIRED_PERMITS = [
        'requiredPermitsKey1' => 'requiredPermitsValue1',
        'requiredPermitsKey2' => 'requiredPermitsValue2'
    ];

    private $irhpPermitApplication;

    private $applicationStep;

    private $qaContextFactory;

    private $questionHandlerDelegator;

    private $handler2;

    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->applicationStep = m::mock(ApplicationStep::class);

        $this->qaContextFactory = m::mock(QaContextFactory::class);

        $this->questionHandlerDelegator = new QuestionHandlerDelegator($this->qaContextFactory);

        $handler1 = m::mock(QuestionHandlerInterface::class);
        $this->questionHandlerDelegator->registerQuestionHandler(40, $handler1);

        $this->handler2 = m::mock(QuestionHandlerInterface::class);
        $this->questionHandlerDelegator->registerQuestionHandler(self::HANDLER_2_QUESTION_ID, $this->handler2);

        $handler3 = m::mock(QuestionHandlerInterface::class);
        $this->questionHandlerDelegator->registerQuestionHandler(20, $handler3);
    }

    public function testDelegate(): void
    {
        $qaContext = m::mock(QaContext::class);

        $this->applicationStep->shouldReceive('getQuestion->getId')
            ->withNoArgs()
            ->andReturn(self::HANDLER_2_QUESTION_ID);

        $this->qaContextFactory->shouldReceive('create')
            ->with($this->applicationStep, $this->irhpPermitApplication)
            ->andReturn($qaContext);

        $this->handler2->shouldReceive('handle')
            ->with($qaContext, self::REQUIRED_PERMITS)
            ->once();

        $this->questionHandlerDelegator->delegate(
            $this->irhpPermitApplication,
            $this->applicationStep,
            self::REQUIRED_PERMITS
        );
    }

    public function testDelegateHandlerNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No question handler specified for question id 99');

        $this->applicationStep->shouldReceive('getQuestion->getId')
            ->withNoArgs()
            ->andReturn(99);

        $this->questionHandlerDelegator->delegate(
            $this->irhpPermitApplication,
            $this->applicationStep,
            self::REQUIRED_PERMITS
        );
    }
}
