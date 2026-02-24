<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationStep as ApplicationStepRepo;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath;
use Dvsa\Olcs\APi\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;
use Dvsa\Olcs\Api\Service\Qa\QaContextGenerator;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\QaEntityProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * QaContextGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QaContextGeneratorTest extends MockeryTestCase
{
    private $slug = 'removals-eligibility';

    private $irhpApplicationId = 77;

    private $irhpPermitApplicationId = 123;

    private $qaEntity;

    private $applicationStepRepo;

    private $qaEntityProvider;

    private $qaContextFactory;

    private $qaContextGenerator;

    public function setUp(): void
    {
        $this->qaEntity = m::mock(QaEntityInterface::class);

        $this->applicationStepRepo = m::mock(ApplicationStepRepo::class);

        $this->qaEntityProvider = m::mock(QaEntityProvider::class);
        $this->qaEntityProvider->shouldReceive('get')
            ->with($this->irhpApplicationId, $this->irhpPermitApplicationId)
            ->andReturn($this->qaEntity);

        $this->qaContextFactory = m::mock(QaContextFactory::class);

        $this->qaContextGenerator = new QaContextGenerator(
            $this->applicationStepRepo,
            $this->qaEntityProvider,
            $this->qaContextFactory
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGenerate')]
    public function testGenerate(mixed $previousApplicationStep, mixed $qaEntityAnswer): void
    {
        $applicationPathId = 44;

        $applicationPath = m::mock(ApplicationPath::class);
        $applicationPath->shouldReceive('getId')
            ->andReturn($applicationPathId);

        $this->qaEntity->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->andReturnTrue();
        $this->qaEntity->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturnTrue();
        $this->qaEntity->shouldReceive('getAnswer')
            ->with($previousApplicationStep)
            ->andReturn($qaEntityAnswer);
        $this->qaEntity->shouldReceive('getActiveApplicationPath')
            ->withNoArgs()
            ->andReturn($applicationPath);

        $applicationStep = m::mock(ApplicationStep::class);
        $applicationStep->shouldReceive('getPreviousApplicationStep')
            ->withNoArgs()
            ->andReturn($previousApplicationStep);

        $this->applicationStepRepo->shouldReceive('fetchByApplicationPathIdAndSlug')
            ->with($applicationPathId, $this->slug)
            ->andReturn($applicationStep);

        $qaContext = m::mock(QaContext::class);

        $this->qaContextFactory->shouldReceive('create')
            ->with($applicationStep, $this->qaEntity)
            ->andReturn($qaContext);

        $returnedQaContext = $this->qaContextGenerator->generate(
            $this->irhpApplicationId,
            $this->irhpPermitApplicationId,
            $this->slug
        );

        $this->assertSame($qaContext, $returnedQaContext);
    }

    public static function dpTestGenerate(): array
    {
        return [
            [null, null],
            [m::mock(ApplicationStep::class), '128']
        ];
    }

    public function testExceptionOnApplicationPathNotEnabled(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(QaContextGenerator::ERR_QA_NOT_SUPPORTED);

        $this->qaEntity->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->andReturnFalse();

        $this->qaContextGenerator->generate($this->irhpApplicationId, $this->irhpPermitApplicationId, $this->slug);
    }

    public function testExceptionOnAlreadySubmitted(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(QaContextGenerator::ERR_ALREADY_SUBMITTED);

        $this->qaEntity->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->andReturnTrue();
        $this->qaEntity->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturnFalse();

        $this->qaContextGenerator->generate($this->irhpApplicationId, $this->irhpPermitApplicationId, $this->slug);
    }

    public function testExceptionOnNotAccessible(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(QaContextGenerator::ERR_NOT_ACCESSIBLE);

        $applicationPathId = 44;

        $applicationPath = m::mock(ApplicationPath::class);
        $applicationPath->shouldReceive('getId')
            ->andReturn($applicationPathId);

        $previousApplicationStep = m::mock(ApplicationStep::class);

        $this->qaEntity->shouldReceive('isApplicationPathEnabled')
            ->withNoArgs()
            ->andReturnTrue();
        $this->qaEntity->shouldReceive('isNotYetSubmitted')
            ->withNoArgs()
            ->andReturnTrue();
        $this->qaEntity->shouldReceive('getAnswer')
            ->with($previousApplicationStep)
            ->andReturn(null);
        $this->qaEntity->shouldReceive('getActiveApplicationPath')
            ->withNoArgs()
            ->andReturn($applicationPath);

        $applicationStep = m::mock(ApplicationStep::class);
        $applicationStep->shouldReceive('getPreviousApplicationStep')
            ->withNoArgs()
            ->andReturn($previousApplicationStep);

        $this->applicationStepRepo->shouldReceive('fetchByApplicationPathIdAndSlug')
            ->with($applicationPathId, $this->slug)
            ->andReturn($applicationStep);

        $this->qaContextGenerator->generate($this->irhpApplicationId, $this->irhpPermitApplicationId, $this->slug);
    }
}
