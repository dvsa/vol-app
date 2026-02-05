<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\TransactionManagerInterface;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Psr\Container\ContainerInterface;

abstract class AbstractCompaniesHouseConsumerTestCase extends AbstractCommandHandlerTestCase
{
    protected function setupService(): void
    {
        $this->repoManager = m::mock(RepositoryServiceManager::class);
        $this->queryHandler = m::mock(QueryHandlerManager::class);
        $this->commandHandler = m::mock(CommandHandlerManager::class);
        $this->identityProvider = m::mock(IdentityProviderInterface::class);
        $this->mockTransationMngr = m::mock(TransactionManagerInterface::class);

        foreach ($this->repoMap as $alias => $service) {
            $this->repoManager
                ->shouldReceive('get')
                ->with($alias)
                ->andReturn($service);
        }

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($this->repoManager);
        $sm->shouldReceive('get')->with('TransactionManager')->andReturn($this->mockTransationMngr);
        $sm->shouldReceive('get')->with('QueryHandlerManager')->andReturn($this->queryHandler);
        $sm->expects('get')->with('CommandHandlerManager')->andReturn($this->commandHandler);
        $sm->shouldReceive('get')->with(IdentityProviderInterface::class)->andReturn($this->identityProvider);

        foreach ($this->mockedSmServices as $serviceName => $service) {
            $sm->shouldReceive('get')->with($serviceName)->andReturn($service);
        }

        $this->sut->__invoke($sm, null);
    }
}
