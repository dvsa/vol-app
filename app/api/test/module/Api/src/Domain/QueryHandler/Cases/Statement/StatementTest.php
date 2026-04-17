<?php

declare(strict_types=1);

/**
 * Statement Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Statement;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Statement\Statement;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Statement as StatementRepo;
use Dvsa\Olcs\Api\Entity\Cases\Statement as StatementEntity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Query\Cases\Statement\Statement as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Statement Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class StatementTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Statement();
        $this->mockRepo('Statement', StatementRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);
        $this->createMockStatement($query, null);

        /** @var Result $result */
        $result = $this->sut->handleQuery($query);
        $this->assertInstanceOf(Result::class, $result);
        $serializedResult = $result->serialize();

        $this->assertIsArray($serializedResult);
        $this->assertArrayHasKey('DUMMY_STATEMENT_KEY', $serializedResult);
        $this->assertSame('DUMMY_STATEMENT_VALUE', $serializedResult['DUMMY_STATEMENT_KEY']);
    }

    public function testHandleQueryWhenNoAssignedCaseworker(): void
    {
        $query = Qry::create(['id' => 1]);
        $this->createMockStatement($query, null);

        /** @var Result $result */
        $result = $this->sut->handleQuery($query);

        $arr = $result->serialize();

        $this->assertArrayHasKey('assignedCaseworker', $arr);
        $this->assertNull($arr['assignedCaseworker']);
    }

    public function testHandleQueryWhenAssignedCaseworker(): void
    {
        $query = Qry::create(['id' => 1]);

        /** @var m\Mock|User $caseworker */
        $caseworker = m::mock(User::class)
            ->shouldReceive('getId')
            ->andReturn('DUMMY_USER_ID')
            ->getMock();

        $this->createMockStatement($query, $caseworker);

        /** @var Result $result */
        $result = $this->sut->handleQuery($query);

        $serializedResult = $result->serialize();
        $this->assertIsArray($serializedResult);
        $this->assertArrayHasKey('assignedCaseworker', $serializedResult);
        $this->assertIsArray($serializedResult['assignedCaseworker']);
        $this->assertArrayHasKey('id', $serializedResult['assignedCaseworker']);
        $this->assertSame('DUMMY_USER_ID', $serializedResult['assignedCaseworker']['id']);
    }

    protected function createMockStatement(mixed $query, mixed $caseworker): mixed
    {
        $statement = m::mock(StatementEntity::class);
        $statement->shouldReceive('getAssignedCaseworker')->andReturn($caseworker);
        $statement->shouldReceive('serialize')
            ->andReturn(['DUMMY_STATEMENT_KEY' => 'DUMMY_STATEMENT_VALUE']);

        $this->repoMap['Statement']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($statement);

        return $statement;
    }
}
