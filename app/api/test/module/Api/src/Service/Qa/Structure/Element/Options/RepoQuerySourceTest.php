<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Domain\Repository\AbstractReadonlyRepository;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\RepoQuerySource;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RepoQuerySourceTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RepoQuerySourceTest extends MockeryTestCase
{
    public function testPopulateOptionList(): void
    {
        $methodName = 'fetchSectorOptions';
        $repoName = 'Sector';

        $options = [
            'method' => $methodName,
            'repo' => $repoName,
        ];

        $item1Value = '1';
        $item1Label = 'Food';
        $item1Hint = 'Hint for the Food item';

        $item2Value = '3';
        $item2Label = 'Metals';
        $item2Hint = 'Hint for the Metals item';

        $queryResponse = [
            [
                'value' => $item1Value,
                'label' => $item1Label,
                'hint' => $item1Hint,
            ],
            [
                'value' => $item2Value,
                'label' => $item2Label,
                'hint' => $item2Hint,
            ],
        ];

        $sectorsRepo = m::mock(AbstractReadonlyRepository::class);
        $sectorsRepo->shouldReceive($methodName)
            ->once()
            ->andReturn($queryResponse);

        $repoServiceManager = m::mock(RepositoryServiceManager::class);
        $repoServiceManager->shouldReceive('get')
            ->with($repoName)
            ->andReturn($sectorsRepo);

        $optionList = m::mock(OptionList::class);
        $optionList->shouldReceive('add')
            ->with($item1Value, $item1Label, $item1Hint)
            ->once()
            ->ordered();
        $optionList->shouldReceive('add')
            ->with($item2Value, $item2Label, $item2Hint)
            ->once()
            ->ordered();

        $refDataSource = new RepoQuerySource($repoServiceManager);
        $refDataSource->populateOptionList($optionList, $options);
    }
}
