<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\Cache\ById as CacheByIdQry;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\ToggleAwareInterface;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Repository\ValidateMockRepoTypeTrait;
use Dvsa\Olcs\Api\Domain\Logger\EntityAccessLogger;

class QueryHandlerTestCase extends MockeryTestCase
{
    use ValidateMockRepoTypeTrait;

    public $entityAccessLogger;

    /**
     * @var (\Mockery\MockInterface & \Psr\Container\ContainerInterface)
     */
    public $container;

    /**
     * @var AbstractQueryHandler
     */
    protected $sut;

    /**
     * @var QueryHandlerManager|m\MockInterface
     */
    protected $queryHandler;

    /** @var  CommandHandlerManager */
    protected $commandHandler;

    /**
     * @var m\MockInterface|RepositoryServiceManager
     */
    protected $repoManager;

    /** @var m\MockInterface[]  */
    protected $repoMap = [];

    /** @var m\MockInterface[]  */
    protected $mockedSmServices = [];

    /** @var array  */
    protected $refData = [];

    /** @var array  */
    protected $references = [];

    /** @var array  */
    protected $categoryReferences = [];

    /** @var array  */
    protected $subCategoryReferences = [];

    /** @var bool  */
    private $initRefdata = false;

    /** @var array  */
    protected $commands = [];

    protected array $sideEffectQueries = [];

    public function setUp(): void
    {
        $this->repoManager = m::mock(RepositoryServiceManager::class);
        $this->commandHandler = m::mock(CommandHandlerManager::class);
        $this->queryHandler = m::mock(QueryHandlerManager::class);
        $this->entityAccessLogger = m::mock(EntityAccessLogger::class)->shouldIgnoreMissing();

        foreach ($this->repoMap as $alias => $service) {
            $this->repoManager
                ->shouldReceive('get')
                ->with($alias)
                ->andReturn($service);
        }

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with('RepositoryServiceManager')->andReturn($this->repoManager);
        $sm->shouldReceive('get')->with('CommandHandlerManager')->andReturn($this->commandHandler);
        $sm->shouldReceive('get')->with(EntityAccessLogger::class)->andReturn($this->entityAccessLogger);
        $sm->expects('get')->with('QueryHandlerManager')->andReturn($this->queryHandler);

        foreach ($this->mockedSmServices as $serviceName => $service) {
            $sm->shouldReceive('get')->with($serviceName)->andReturn($service);
        }

        // if not already mocked AuthorizationService then do it
        if (!isset($this->mockedSmServices[AuthorizationService::class])) {
            $sm->shouldReceive('get')->with(AuthorizationService::class)
                ->andReturn(
                    m::mock(AuthorizationService::class)->shouldReceive('isGranted')->andReturn(false)->getMock()
                );
        }

        /**
         * If the handler is toggle aware, provide this for free. For more more complex testing use
         * $this->mockedSmServices in the extending class
         */
        if (
            $this->sut instanceof ToggleRequiredInterface || $this->sut instanceof ToggleAwareInterface
            && !array_key_exists(ToggleService::class, $this->mockedSmServices)
        ) {
            $toggleService = m::mock(ToggleService::class);
            $sm->shouldReceive('get')->with(ToggleService::class)->andReturn($toggleService);
        }

        $this->initReferences();

        $this->commands = [];

        $this->container = $sm;
        $this->sut = $this->sut->__invoke($sm, null);
    }

    protected function initReferences(): void
    {
        if (!$this->initRefdata) {
            foreach ($this->refData as $id => $mock) {
                if (is_numeric($id) && is_string($mock)) {
                    $this->refData[$mock] = m::mock(RefData::class)->makePartial()->setId($mock);
                } else {
                    $mock->makePartial();
                    $mock->setId($id);
                }
            }

            foreach ($this->categoryReferences as $id => $mock) {
                $mock->makePartial();
                $mock->setId($id);
            }

            foreach ($this->subCategoryReferences as $id => $mock) {
                $mock->makePartial();
                $mock->setId($id);
            }

            foreach ($this->references as $mocks) {
                foreach ($mocks as $id => $mock) {
                    if ($mock instanceof m\MockInterface) {
                        $mock->makePartial();
                    }

                    $mock->setId($id);
                }
            }

            $this->initRefdata = true;
        }
    }

    public function mapRefData(mixed $key): mixed
    {
        return $this->refData[$key] ?? null;
    }

    public function mapCategoryReference(mixed $key): mixed
    {
        return $this->categoryReferences[$key] ?? null;
    }

    public function mapSubCategoryReference(mixed $key): mixed
    {
        return $this->subCategoryReferences[$key] ?? null;
    }

    public function mapReference(mixed $class, mixed $id): mixed
    {
        return $this->references[$class][$id] ?? null;
    }

    protected function assertPostConditions(): void
    {
        $this->assertCommandData();
        $this->assertQueryData();
        parent::assertPostConditions();
    }

    protected function mockRepo(mixed $name, mixed $class): mixed
    {
        if (!$class instanceof m\MockInterface) {
            $class = m::mock($class);
        }

        $this->validateMockRepoType($name, $class);

        //if statements here are for BC. We have some existing tests which implement this themselves
        if (!empty($this->refData)) {
            $class->shouldReceive('getRefdataReference')->andReturnUsing($this->mapRefData(...));
        }

        if (!empty($this->references)) {
            $class->shouldReceive('getReference')->andReturnUsing($this->mapReference(...));
        }

        if (!empty($this->categoryReferences)) {
            $class->shouldReceive('getCategoryReference')->andReturnUsing($this->mapCategoryReference(...));
        }

        if (!empty($this->subCategoryReferences)) {
            $class->shouldReceive('getSubCategoryReference')->andReturnUsing($this->mapSubCategoryReference(...));
        }

        $this->repoMap[$name] = $class;

        return $class;
    }

    public function expectedSideEffect(mixed $class, mixed $data, mixed $result, int $times = 1): void
    {
        $this->commandHandler->shouldReceive('handleCommand')
            ->times($times)
            ->with(m::type($class))
            ->andReturnUsing(
                function (CommandInterface $command) use ($class, $data, $result) {
                    $this->commands[] = [$command, $data];
                    return $result;
                }
            );
    }

    /**
     * @NOTE must be called after the tested method has been executed
     */
    private function assertCommandData(): void
    {
        foreach ($this->commands as $command) {
            /** @var CommandInterface $cmd */
            [$cmd, $data] = $command;

            $cmdData = $cmd->getArrayCopy();
            $cmdDataToMatch = [];

            foreach ($data as $key => $value) {
                unset($value);
                $cmdDataToMatch[$key] = $cmdData[$key] ?? null;
            }

            $this->assertEquals($data, $cmdDataToMatch, $cmd::class . ' has unexpected data');
        }
    }

    /**
     * @NOTE must be called after the tested method has been executed
     */
    private function assertQueryData(): void
    {
        foreach ($this->sideEffectQueries as $query) {
            /** @var QueryInterface $qry */
            [$qry, $data] = $query;

            $qryData = $qry->getArrayCopy();
            $qryDataToMatch = [];

            foreach ($data as $key => $value) {
                $qryDataToMatch[$key] = $qryData[$key] ?? null;
            }

            $this->assertEquals($data, $qryDataToMatch, $qry::class . ' has unexpected data');
        }
    }

    public function expectedQuery(mixed $class, array $data = [], mixed $result = null, int $times = 1): void
    {
        if ($result === null) {
            $result = new Result();
        }

        $this->queryHandler->expects('handleQuery')
            ->times($times)
            ->with(m::type($class))
            ->andReturnUsing(
                function ($query) use ($class, $data, $result) {
                    $this->sideEffectQueries[] = [$query, $data];
                    return $result;
                }
            );
    }

    public function expectedCacheCall(mixed $cacheId, mixed $uniqueId = null, mixed $result = null, int $times = 1): void
    {
        $data = [
            'id' => $cacheId,
            'uniqueId' => $uniqueId,
        ];

        $this->expectedQuery(CacheByIdQry::class, $data, $result, $times);
    }

    public function expectedUserDataCacheCall(mixed $result = null, int $times = 1): void
    {
        $this->expectedQuery(MyAccount::class, [], $result, $times);
    }

    protected function setupIsInternalUser(bool $isInternalUser = true): void
    {
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->atLeast()->once()
            ->andReturn($isInternalUser);
    }
}
