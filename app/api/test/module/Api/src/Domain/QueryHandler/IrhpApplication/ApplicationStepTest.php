<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Element\SelfservePage;
use Dvsa\Olcs\Api\Service\Qa\Element\SelfservePageGenerator;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaContextGenerator;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationStep as ApplicationStepQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class ApplicationStepTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ApplicationStep();

        $this->mockedSmServices = [
            'QaContextGenerator' => m::mock(QaContextGenerator::class),
            'QaSelfservePageGenerator' => m::mock(SelfservePageGenerator::class),
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $irhpApplicationId = 23;
        $irhpPermitApplicationId = 457;
        $slug = 'removals-eligibility';

        $query = ApplicationStepQry::create(
            [
                'id' => $irhpApplicationId,
                'irhpPermitApplication' => $irhpPermitApplicationId,
                'slug' => $slug,
            ]
        );

        $qaContext = m::mock(QaContext::class);

        $this->mockedSmServices['QaContextGenerator']->shouldReceive('generate')
            ->with($irhpApplicationId, $irhpPermitApplicationId, $slug)
            ->andReturn($qaContext);

        $selfservePage = m::mock(SelfservePage::class);

        $this->mockedSmServices['QaSelfservePageGenerator']->shouldReceive('generate')
            ->with($qaContext)
            ->once()
            ->andReturn($selfservePage);

        $selfservePageRepresentation = ['selfservePageRepresentation'];

        $selfservePage->shouldReceive('getRepresentation')
            ->andReturn($selfservePageRepresentation);

        $this->assertEquals(
            $selfservePageRepresentation,
            $this->sut->handleQuery($query)
        );
    }
}
