<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\Query\User\UserListByTrafficArea;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\QueryHandler\User\UserList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\User as Repo;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Query\User\UserList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use ReflectionClass;

/**
 * @see QueryHandler
 */
class UserListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('User', Repo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('handleQueryProvider')]
    public function testHandleQuery(mixed $isInternal, mixed $canAccessAll, mixed $queryData): void
    {
        $userData = [
            'isInternal' => $isInternal,
            'dataAccess' => [
                'canAccessAll' => $canAccessAll,
            ]
        ];

        $this->expectedUserDataCacheCall($userData);

        $query = Query::create($queryData);

        $user = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $user->setId(74);

        $reflectionClass = new ReflectionClass(UserEntity::class);
        $property = $reflectionClass->getProperty('userType');
        $property->setValue($user, User::USER_TYPE_OPERATOR);

        $this->repoMap['User']->shouldReceive('fetchList')->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->andReturn([$user]);
        $this->repoMap['User']->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(74, $result['result'][0]['id']);
        $this->assertSame('COUNT', $result['count']);
    }

    public static function handleQueryProvider(): array
    {
        return [
            'not internal and does not have full access' => [false, false, ['QUERY']],
            'is internal and does have full access' => [true, true, ['QUERY']],
            'is internal and has an org id' => [true, true, ['organisation' => 888]],
        ];
    }

    public function testRedirectWhenInternalAndNotFullAccess(): void
    {
        $trafficAreas = ['B','C'];

        $initialQueryData = [
            'sort' => 'sort-field',
            'order' => 'ASC',
            'team' => 999,
            'organisation' => null,
            'isInternal' => true
        ];

        $additionalData = [
            'trafficAreas' => $trafficAreas,
        ];

        $newQueryData = array_merge($initialQueryData, $additionalData);

        $query = Query::create($initialQueryData);

        $userData = [
            'isInternal' => true,
            'dataAccess' => [
                'canAccessAll' => false,
                'trafficAreas' => $trafficAreas
            ]
        ];

        $queryResult = new Result();
        $this->expectedUserDataCacheCall($userData);
        $this->expectedQuery(UserListByTrafficArea::class, $newQueryData, $queryResult);
        $this->assertSame($queryResult, $this->sut->handleQuery($query));
    }
}
