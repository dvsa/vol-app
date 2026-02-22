<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\User;

use DateTimeImmutable;
use Dvsa\Olcs\Api\Domain\Query\User\UserListSelfserve as ListDto;
use Dvsa\Olcs\Api\Domain\QueryHandler\User\OperatorAdminForOrganisationHasLoggedIn as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\User\OperatorAdminForOrganisationHasLoggedIn as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
class OperatorAdminForOrganisationHasLoggedInTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo(Repository\User::class, Repository\User::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public static function dpHandleQuery_OrganisationHasOperatorAdminsWhoHaveLoggedIn(): array
    {
        return [
            'HasLoggedIn' => [
                'fetchCountResult' => 1,
                'expectedOperatorAdminHasLoggedIn' => true,
            ],
            'HasNotLoggedIn' => [
                'fetchCountResult' => 0,
                'expectedOperatorAdminHasLoggedIn' => false,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery_OrganisationHasOperatorAdminsWhoHaveLoggedIn')]
    public function testHandleQuery_OrganisationHasOperatorAdminsWhoHaveLoggedIn(int $fetchCountResult, bool $expectedOperatorAdminHasLoggedIn): void
    {
        $organisationId = 1;

        $query = Query::create(['organisation' => $organisationId]);

        $repo = $this->repoMap[Repository\User::class];
        $repo->shouldReceive('fetchCount')
            ->with(m::type(ListDto::class))
            ->andReturn($fetchCountResult);

        $result = $this->sut->handleQuery($query);
        $this->assertArrayHasKey('operatorAdminHasLoggedIn', $result);
        $this->assertEquals($expectedOperatorAdminHasLoggedIn, $result['operatorAdminHasLoggedIn']);
    }

    public static function dpHandleQuery_UsesLastLoggedInFromFromQueryIfProvided(): array
    {
        return [
            'LastLoggedInFrom not specified' => [
                'expectedLastLoggedInFrom' => QueryHandler::DEFAULT_LAST_LOGGED_IN_FROM,
                'query' => Query::create(['organisation' => 1]),
            ],
            'LastLoggedInFrom specified' => [
                'expectedLastLoggedInFrom' => $specifiedDate = '2020-01-01',
                'query' => Query::create(['organisation' => 1, 'lastLoggedInFrom' => $specifiedDate]),
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery_UsesLastLoggedInFromFromQueryIfProvided')]
    public function testHandleQuery_UsesLastLoggedInFromFromQueryIfProvided(string $expectedLastLoggedInFrom, Query $query): void
    {
        $repo = $this->repoMap[Repository\User::class];
        $repo->shouldReceive('fetchCount')
            ->withArgs(fn(ListDto $dto) => $dto->getLastLoggedInFrom() === $expectedLastLoggedInFrom)
            ->andReturn(0);

        $result = $this->sut->handleQuery($query);
        $this->assertArrayHasKey('lastLoggedInFrom', $result);
        $this->assertEquals($expectedLastLoggedInFrom, $result['lastLoggedInFrom']);
    }

    public function testHandleQuery_ResultContainsOrganisationIdFromQuery(): void
    {
        $organisationId = 1;

        $query = Query::create(['organisation' => $organisationId]);

        $repo = $this->repoMap[Repository\User::class];
        $repo->shouldReceive('fetchCount')
            ->with(m::type(ListDto::class))
            ->andReturn(0);

        $result = $this->sut->handleQuery($query);
        $this->assertArrayHasKey('organisation', $result);
        $this->assertEquals($organisationId, $result['organisation']);
    }

    public function testHandleQuery_ThrowsExceptionIfOrganisationIdNotProvided(): void
    {
        $this->expectExceptionMessage('Organisation ID is required');
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $this->sut->handleQuery(Query::create([]));
    }

    public function testHandleQuery_ThrowsExceptionIfQueryIsNotInstanceOfOperatorAdminForOrganisationHasLoggedIn(): void
    {
        $this->expectExceptionMessage('Expected instance of: ' . Query::class);
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $instance = new class () extends \stdClass implements QueryInterface {
            public function exchangeArray(array $array): void
            {
                // phpcs:ignore Generic.Commenting.Todo.TaskFound
                // TODO: Implement exchangeArray() method.
            }

            public function getArrayCopy(): void
            {
                // phpcs:ignore Generic.Commenting.Todo.TaskFound
                // TODO: Implement getArrayCopy() method.
            }

            public static function create(array $data): void
            {
                // phpcs:ignore Generic.Commenting.Todo.TaskFound
                // TODO: Implement create() method.
            }
        };

        $this->sut->handleQuery($instance);
    }
}
