<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Db\Service\Search;

use Dvsa\Olcs\Api\Domain\Repository\SystemParameter;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Db\Service\Search\Search;
use Dvsa\Olcs\Db\Service\Search\SearchFactory;
use Elastica\Client as ElasticaClient;
use Elasticsearch\Client;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

class SearchFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockElastica = m::mock(ElasticaClient::class);
        $mockAuth = m::mock(AuthorizationService::class);
        $mockSystemParamRepo = m::mock(SystemParameter::class);

        $mockRepoServiceManager = m::mock(RepositoryServiceManager::class);
        $mockRepoServiceManager->expects('get')->with('SystemParameter')->andReturn($mockSystemParamRepo);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(Client::class)->andReturn($mockElastica);
        $container->expects('get')->with(AuthorizationService::class)->andReturn($mockAuth);
        $container->expects('get')->with('RepositoryServiceManager')->andReturn($mockRepoServiceManager);

        $sut = new SearchFactory();
        $this->assertInstanceOf(Search::class, $sut->__invoke($container, Search::class));
    }
}
