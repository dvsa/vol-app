<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
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

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('da.token', ':token')->once()->andReturn('tokenEq');
        $mockQb->shouldReceive('andWhere')->with('tokenEq')->once()->andReturnSelf();
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
        $mockQb->shouldReceive('expr->eq')->andReturn('tokenEq');
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
        $mockQb->shouldReceive('expr->eq')->andReturn('tokenEq');
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
}
