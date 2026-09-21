<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use Mockery as m;
use Symfony\Component\Uid\UuidV7;

final class DocumentAnalysisTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(DocumentAnalysisRepo::class);
    }

    /**
     * The token parameter is bound as BINARY rather than left to inference: the ORM types any
     * PHP string as STRING, which puts raw uid bytes through the connection's character set.
     */
    public function testFetchByTokenBindsTheTokenAsBinary(): void
    {
        $token = (new UuidV7())->toBinary();
        $entity = new Entity();

        $tokenEq = new Comparison('da.token', '=', ':token');

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('da.token', ':token')->once()->andReturn($tokenEq);
        $mockQb->shouldReceive('andWhere')->with($tokenEq)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('token', $token, Types::BINARY)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setMaxResults')->with(1)->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getOneOrNullResult')
            ->with(Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($entity);

        $sut = m::mock(DocumentAnalysisRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame($entity, $sut->fetchByToken($token));
    }

    public function testFetchByTokenReturnsNullWhenNoRowMatches(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->andReturn(new Comparison('da.token', '=', ':token'));
        $mockQb->shouldReceive('andWhere')->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->andReturnSelf();
        $mockQb->shouldReceive('setMaxResults')->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getOneOrNullResult')->once()->andReturnNull();

        $sut = m::mock(DocumentAnalysisRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertNull($sut->fetchByToken((new UuidV7())->toBinary()));
    }

    /** A raw uid can contain NUL and other non-UTF-8 bytes; nothing may reinterpret them. */
    public function testFetchByTokenPassesTheTokenBytesThroughUntouched(): void
    {
        $token = hex2bin('0199000000007000800000000000ff00');
        self::assertIsString($token);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->andReturn(new Comparison('da.token', '=', ':token'));
        $mockQb->shouldReceive('andWhere')->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->withArgs(function (string $name, string $value, string $type) use ($token): bool {
                return $name === 'token' && $value === $token && $type === Types::BINARY;
            })
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setMaxResults')->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getOneOrNullResult')->once()->andReturnNull();

        $sut = m::mock(DocumentAnalysisRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $sut->fetchByToken($token);
    }

    public function testRecordSuccessIsGuardedByIdAndPendingStatus(): void
    {
        $result = ['checks' => ['passed' => true]];
        $metadata = ['bucket' => 'b', 'key' => 'k'];

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('update')->with(Entity::class, 'da')->once()->andReturnSelf();
        $mockQb->shouldReceive('set')->with('da.status', ':success')->once()->andReturnSelf();
        $mockQb->shouldReceive('set')->with('da.result', ':result')->once()->andReturnSelf();
        $mockQb->shouldReceive('set')->with('da.resultMetadata', ':metadata')->once()->andReturnSelf();
        $mockQb->shouldReceive('set')->with('da.completedAt', ':now')->once()->andReturnSelf();
        $idEq = new Comparison('da.id', '=', ':id');
        $pendingEq = new Comparison('da.status', '=', ':pending');
        $mockQb->shouldReceive('expr->eq')->with('da.id', ':id')->once()->andReturn($idEq);
        $mockQb->shouldReceive('expr->eq')->with('da.status', ':pending')->once()->andReturn($pendingEq);
        $mockQb->shouldReceive('where')->with($idEq)->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with($pendingEq)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('success', Entity::STATUS_SUCCESS)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('result', $result, Types::JSON)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('metadata', $metadata, Types::JSON)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('now', m::type(\DateTime::class))->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('id', 5)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('pending', Entity::STATUS_PENDING)->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn(1);

        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame(1, $this->sut->recordSuccess(5, $result, $metadata));
    }

    public function testRecordSuccessReturnsZeroWhenRowNoLongerPending(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('update')->andReturnSelf();
        $mockQb->shouldReceive('set')->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->andReturn(new Comparison('da.id', '=', ':id'));
        $mockQb->shouldReceive('where')->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->andReturnSelf();
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn(0);

        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame(0, $this->sut->recordSuccess(5, [], []));
    }

    public function testRecordErrorIsGuardedByIdAndPendingStatus(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('update')->with(Entity::class, 'da')->once()->andReturnSelf();
        $mockQb->shouldReceive('set')->with('da.status', ':error')->once()->andReturnSelf();
        $mockQb->shouldReceive('set')->with('da.errorDetail', ':errorDetail')->once()->andReturnSelf();
        $mockQb->shouldReceive('set')->with('da.completedAt', ':now')->once()->andReturnSelf();
        $idEq = new Comparison('da.id', '=', ':id');
        $pendingEq = new Comparison('da.status', '=', ':pending');
        $mockQb->shouldReceive('expr->eq')->with('da.id', ':id')->once()->andReturn($idEq);
        $mockQb->shouldReceive('expr->eq')->with('da.status', ':pending')->once()->andReturn($pendingEq);
        $mockQb->shouldReceive('where')->with($idEq)->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with($pendingEq)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('error', Entity::STATUS_ERROR)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('errorDetail', 'bad JSON')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('now', m::type(\DateTime::class))->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('id', 7)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('pending', Entity::STATUS_PENDING)->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->execute')->once()->andReturn(1);

        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame(1, $this->sut->recordError(7, 'bad JSON'));
    }

    public function testFetchAnalysesFiltersByApplicationOnly(): void
    {
        $query = \Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList::create(['application' => 8]);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('andWhere')
            ->with('IDENTITY(da.application) = :applicationId')
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('applicationId', 8)->once()->andReturnSelf();
        $mockQb->shouldReceive('orderBy')->with('da.createdOn', 'DESC')->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['row1', 'row2']);

        $sut = m::mock(DocumentAnalysisRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame(['row1', 'row2'], $sut->fetchAnalyses($query));
    }

    public function testFetchAnalysesFiltersByDocumentOnly(): void
    {
        $query = \Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList::create(['document' => 123]);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('andWhere')
            ->with('IDENTITY(da.document) = :documentId')
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('documentId', 123)->once()->andReturnSelf();
        $mockQb->shouldReceive('orderBy')->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn([]);

        $sut = m::mock(DocumentAnalysisRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame([], $sut->fetchAnalyses($query));
    }

    public function testFetchAnalysesFiltersByStatusOnly(): void
    {
        $query = \Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList::create(['status' => 'PENDING']);

        $statusEq = new Comparison('da.status', '=', ':status');

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('da.status', ':status')->once()->andReturn($statusEq);
        $mockQb->shouldReceive('andWhere')->with($statusEq)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('status', 'PENDING')->once()->andReturnSelf();
        $mockQb->shouldReceive('orderBy')->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn([]);

        $sut = m::mock(DocumentAnalysisRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame([], $sut->fetchAnalyses($query));
    }

    public function testFetchAnalysesWithNoQueryOnlyOrders(): void
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('andWhere')->never();
        $mockQb->shouldReceive('setParameter')->never();
        $mockQb->shouldReceive('orderBy')->with('da.createdOn', 'DESC')->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn([]);

        $sut = m::mock(DocumentAnalysisRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame([], $sut->fetchAnalyses(null));
    }

    public function testFetchAnalysesConvertsIteratorResultToArray(): void
    {
        $query = \Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList::create(['application' => 8]);

        $iterator = new \ArrayIterator(['row1', 'row2']);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->once()->andReturnSelf();
        $mockQb->shouldReceive('orderBy')->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn($iterator);

        $sut = m::mock(DocumentAnalysisRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->shouldReceive('createQueryBuilder')->once()->andReturn($mockQb);

        $this->assertSame(['row1', 'row2'], $sut->fetchAnalyses($query));
    }
}
