<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitStock;

use DateTime;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitStock\AvailableBilateral;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationPathGroup as ApplicationPathGroupRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\AvailableBilateral as AvailableBilateralQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class AvailableBilateralTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AvailableBilateral();
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);
        $this->mockRepo('ApplicationPathGroup', ApplicationPathGroupRepo::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $countryId = 'NO';

        $stock1ApplicationPathGroupId = 57;
        $stock1FirstStepSlug = 'bi-permit-usage';

        $stock2ApplicationPathGroupId = 23;
        $stock2FirstStepSlug = 'bi-number-of-permits-morocco';

        $stocks = [
            [
                'stock_1_field_1' => 'stock_1_value_1',
                'stock_1_field_2' => 'stock_1_value_2',
                'application_path_group_id' => $stock1ApplicationPathGroupId
            ],
            [
                'stock_2_field_1' => 'stock_2_value_1',
                'stock_2_field_2' => 'stock_2_value_2',
                'application_path_group_id' => $stock2ApplicationPathGroupId
            ]
        ];

        $expectedAugmentedStocks = [
            [
                'stock_1_field_1' => 'stock_1_value_1',
                'stock_1_field_2' => 'stock_1_value_2',
                'application_path_group_id' => $stock1ApplicationPathGroupId,
                'first_step_slug' => $stock1FirstStepSlug
            ],
            [
                'stock_2_field_1' => 'stock_2_value_1',
                'stock_2_field_2' => 'stock_2_value_2',
                'application_path_group_id' => $stock2ApplicationPathGroupId,
                'first_step_slug' => $stock2FirstStepSlug
            ]
        ];

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchOpenBilateralStocksByCountry')
            ->with($countryId, m::type(DateTime::class))
            ->andReturn($stocks);

        $stock1ApplicationPathGroup = m::mock(ApplicationPathGroup::class);
        $stock1ApplicationPathGroup
            ->shouldReceive('getActiveApplicationPath->getApplicationSteps->first->getQuestion->getSlug')
            ->andReturn($stock1FirstStepSlug);

        $stock2ApplicationPathGroup = m::mock(ApplicationPathGroup::class);
        $stock2ApplicationPathGroup
            ->shouldReceive('getActiveApplicationPath->getApplicationSteps->first->getQuestion->getSlug')
            ->andReturn($stock2FirstStepSlug);

        $this->repoMap['ApplicationPathGroup']->shouldReceive('fetchById')
            ->with($stock1ApplicationPathGroupId)
            ->andReturn($stock1ApplicationPathGroup);
        $this->repoMap['ApplicationPathGroup']->shouldReceive('fetchById')
            ->with($stock2ApplicationPathGroupId)
            ->andReturn($stock2ApplicationPathGroup);

        $query = AvailableBilateralQuery::create(['country' => $countryId]);

        $this->assertEquals(
            $expectedAugmentedStocks,
            $this->sut->handleQuery($query)
        );
    }
}
