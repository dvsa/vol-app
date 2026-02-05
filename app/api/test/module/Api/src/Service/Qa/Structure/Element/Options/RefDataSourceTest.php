<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Domain\Repository\RefData as RefDataRepository;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\RefDataSource;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RefDataSourceTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RefDataSourceTest extends MockeryTestCase
{
    public function testPopulateOptionList(): void
    {
        $categoryId = 'permit_application_status';

        $options = [
            'categoryId' => $categoryId
        ];

        $refData1Id = 'permit_app_uc';
        $refData1Description = 'Under Consideration';
        $refData1Entity = m::mock(RefData::class);
        $refData1Entity->shouldReceive('getId')
            ->andReturn($refData1Id);
        $refData1Entity->shouldReceive('getDescription')
            ->andReturn($refData1Description);

        $refData2Id = 'permit_app_nys';
        $refData2Description = 'Not Yet Submitted';
        $refData2Entity = m::mock(RefData::class);
        $refData2Entity->shouldReceive('getId')
            ->andReturn($refData2Id);
        $refData2Entity->shouldReceive('getDescription')
            ->andReturn($refData2Description);

        $refDataEntities = [
            $refData1Entity,
            $refData2Entity
        ];

        $refDataRepo = m::mock(RefDataRepository::class);
        $refDataRepo->shouldReceive('fetchByCategoryId')
            ->with($categoryId)
            ->andReturn($refDataEntities);

        $optionList = m::mock(OptionList::class);
        $optionList->shouldReceive('add')
            ->with($refData1Id, $refData1Description)
            ->once()
            ->ordered();
        $optionList->shouldReceive('add')
            ->with($refData2Id, $refData2Description)
            ->once()
            ->ordered();

        $refDataSource = new RefDataSource($refDataRepo);
        $refDataSource->populateOptionList($optionList, $options);
    }
}
