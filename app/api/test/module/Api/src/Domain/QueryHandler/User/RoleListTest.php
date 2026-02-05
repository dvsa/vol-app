<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Mockery as m;
use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\User\RoleList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Role as RoleRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Transfer\Query\User\RoleList as Qry;

class RoleListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RoleList();
        $this->mockRepo('Role', RoleRepo::class);
        $this->mockedSmServices[ToggleService::class] = m::mock(ToggleService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $this->mockedSmServices[ToggleService::class]->expects('isDisabled')
            ->with(FeatureToggle::TRANSPORT_CONSULTANT_ROLE)
            ->andReturnFalse();

        $this->repoMap['Role']->expects('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->andReturn(
                [
                    m::mock(BundleSerializableInterface::class)
                        ->expects('serialize')
                        ->andReturn(['role' => Role::ROLE_OPERATOR_TC])
                        ->getMock(),
                    m::mock(BundleSerializableInterface::class)
                        ->expects('serialize')
                        ->andReturn(['role' => Role::ROLE_OPERATOR_ADMIN])
                        ->getMock()
                ]
            );

        $this->repoMap['Role']->expects('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals([['role' => Role::ROLE_OPERATOR_TC], ['role' => Role::ROLE_OPERATOR_ADMIN]], $result['result']);
    }

    public function testHandleQueryTcConsultantDisabled(): void
    {
        $query = Qry::create([]);

        $this->mockedSmServices[ToggleService::class]->expects('isDisabled')
            ->with(FeatureToggle::TRANSPORT_CONSULTANT_ROLE)
            ->andReturnTrue();

        $this->repoMap['Role']->expects('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->andReturn(
                [
                    m::mock(BundleSerializableInterface::class)
                        ->expects('serialize')
                        ->andReturn(['role' => Role::ROLE_OPERATOR_TC])
                        ->getMock(),
                    m::mock(BundleSerializableInterface::class)
                        ->expects('serialize')
                        ->andReturn(['role' => Role::ROLE_OPERATOR_ADMIN])
                        ->getMock()
                ]
            );

        $this->repoMap['Role']->expects('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(1, $result['count']);
        $this->assertEquals([1 => ['role' => Role::ROLE_OPERATOR_ADMIN]], $result['result']);
    }
}
