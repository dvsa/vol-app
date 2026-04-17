<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use DateTime;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\ApplicationPath;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath as ApplicationPathEntity;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\Element\FormFragment;
use Dvsa\Olcs\Api\Service\Qa\Element\FormFragmentGenerator;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationPath as ApplicationPathQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class ApplicationPathTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ApplicationPath();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'QaFormFragmentGenerator' => m::mock(SelfservePageGenerator::class),
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $irhpApplicationId = 458;

        $query = ApplicationPathQry::create(
            [
                'id' => $irhpApplicationId,
            ]
        );

        $applicationStepEntity1 = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity2 = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity3 = m::mock(ApplicationStepEntity::class);

        $applicationStepEntities = [
            $applicationStepEntity1,
            $applicationStepEntity2,
            $applicationStepEntity3,
        ];

        $applicationPathEntity = m::mock(ApplicationPathEntity::class);
        $applicationPathEntity->shouldReceive('getApplicationSteps->getValues')
            ->andReturn($applicationStepEntities);

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('getActiveApplicationPath')
            ->andReturn($applicationPathEntity);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplicationEntity);

        $formFragment = m::mock(FormFragment::class);
        $formFragmentRepresentation = ['formFragmentRepresentation'];
        $formFragment->shouldReceive('getRepresentation')
            ->andReturn($formFragmentRepresentation);

        $this->mockedSmServices['QaFormFragmentGenerator']->shouldReceive('generate')
            ->with($applicationStepEntities, $irhpApplicationEntity)
            ->once()
            ->andReturn($formFragment);

        $this->assertEquals(
            $formFragmentRepresentation,
            $this->sut->handleQuery($query)
        );
    }
}
