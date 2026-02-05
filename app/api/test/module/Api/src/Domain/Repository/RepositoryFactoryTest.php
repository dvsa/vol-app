<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\DbQueryServiceManager;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryFactory;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Domain\Repository\Category;

class RepositoryFactoryTest extends MockeryTestCase
{
    protected RepositoryFactory $sut;

    public function setUp(): void
    {
        $this->sut = new RepositoryFactory();
    }

    public function testInvoke(): void
    {
        $container = m::mock(ContainerInterface::class);

        $repoManager = m::mock(RepositoryServiceManager::class);
        $em = m::mock(EntityManager::class);
        $qb = m::mock(QueryBuilder::class);
        $dbs = m::mock(DbQueryServiceManager::class);

        $container->expects('get')->with('doctrine.entitymanager.orm_default')->andReturn($em);
        $container->expects('get')->with('QueryBuilder')->andReturn($qb);
        $container->expects('get')->with('DbQueryServiceManager')->andReturn($dbs);
        $container->expects('get')->with('RepositoryServiceManager')->andReturn($repoManager);

        $service = $this->sut->__invoke($container, 'Application');

        $this->assertInstanceOf(Application::class, $service);
    }

    public function testInvokeWithFqcn(): void
    {
        $container = m::mock(ContainerInterface::class);

        $repoManager = m::mock(RepositoryServiceManager::class);
        $em = m::mock(EntityManager::class);
        $qb = m::mock(QueryBuilder::class);
        $dbs = m::mock(DbQueryServiceManager::class);

        $container->expects('get')->with('doctrine.entitymanager.orm_default')->andReturn($em);
        $container->expects('get')->with('QueryBuilder')->andReturn($qb);
        $container->expects('get')->with('DbQueryServiceManager')->andReturn($dbs);
        $container->expects('get')->with('RepositoryServiceManager')->andReturn($repoManager);

        $service = $this->sut->__invoke($container, Application::class);

        $this->assertInstanceOf(Application::class, $service);
    }
}
