<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\Query\User\UserListInternalByTrafficArea;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\QueryHandler\User\UserListInternal as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\User as Repo;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Query\User\UserListInternal as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use ReflectionClass;

/**
 * @see QueryHandler
 */
class UserListInternalTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('User', Repo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $userData = [
            'dataAccess' => [
                'canAccessAll' => true,
            ]
        ];

        $this->expectedUserDataCacheCall($userData);

        $user = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $user->setId(74);
        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($user, \Dvsa\Olcs\Api\Entity\User\User::USER_TYPE_INTERNAL);

        $this->repoMap['User']->shouldReceive('fetchList')->andReturn([$user]);
        $this->repoMap['User']->shouldReceive('fetchCount')->andReturn('COUNT');

        $query = Query::create(['QUERY']);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(74, $result['result'][0]['id']);
        $this->assertSame('COUNT', $result['count']);
    }

    public function testRedirectWhenNotFullAccess(): void
    {
        $trafficAreas = ['B','C'];

        $initialQueryData = [
            'sort' => 'sort-field',
            'order' => 'ASC',
            'excludeLimitedReadOnly' => true,
            'team' => 999,
        ];

        $additionalData = [
            'trafficAreas' => $trafficAreas,
        ];

        $newQueryData = array_merge($initialQueryData, $additionalData);

        $query = Query::create($initialQueryData);

        $userData = [
            'dataAccess' => [
                'canAccessAll' => false,
                'trafficAreas' => $trafficAreas
            ]
        ];

        $queryResult = new Result();
        $this->expectedUserDataCacheCall($userData);
        $this->expectedQuery(UserListInternalByTrafficArea::class, $newQueryData, $queryResult);
        $this->assertSame($queryResult, $this->sut->handleQuery($query));
    }
}
