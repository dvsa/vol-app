<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Toggle;

use Dvsa\Olcs\Api\Domain\Query\FeatureToggle\FetchList;
use Dvsa\Olcs\Api\Domain\Repository\FeatureToggle as FeatureToggleRepo;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle as FeatureToggleEntity;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\Olcs\Api\Service\Toggle\ToggleServiceFactory;
use Mockery as m;
use Psr\Container\ContainerInterface;

class ToggleServiceFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInvoke(): void
    {
        $configName1 = 'config name 1';
        $configName2 = 'config name 2';

        $dbConfig = [
            0 => [
                'friendlyName' => 'friendly name 1',
                'configName' => $configName1,
                'status' => [
                    'id' => FeatureToggleEntity::ACTIVE_STATUS
                ]
            ],
            1 => [
                'friendlyName' => 'friendly name 2',
                'configName' => $configName2,
                'status' => [
                    'id' => FeatureToggleEntity::INACTIVE_STATUS
                ]
            ]
        ];

        $featureToggleRepo = m::mock(FeatureToggleRepo::class);
        $featureToggleRepo->shouldReceive('fetchList')
            ->once()
            ->with(m::type(FetchList::class))
            ->andReturn($dbConfig);

        $repoServiceManager = m::mock(RepositoryServiceManager::class);
        $repoServiceManager->shouldReceive('get')
            ->once()
            ->with('FeatureToggle')
            ->andReturn($featureToggleRepo);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')
            ->once()
            ->with('RepositoryServiceManager')
            ->andReturn($repoServiceManager);

        $sut = new ToggleServiceFactory();
        $toggleService = $sut->__invoke($mockSl, ToggleService::class);

        $this->assertInstanceOf(ToggleService::class, $toggleService);

        //shouldn't do this, but it's the best way to check the config is transferred accurately
        //from DB to toggle manager
        $this->assertEquals(true, $toggleService->isEnabled($configName1));
        $this->assertEquals(false, $toggleService->isEnabled($configName2));
    }
}
