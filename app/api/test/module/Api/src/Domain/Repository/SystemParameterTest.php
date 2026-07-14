<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as SystemParameterEntity;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\SystemParameter::class)]
final class SystemParameterTest extends RepositoryTestCase
{
    /** @var  SystemParameterRepo */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(SystemParameterRepo::class);
    }

    public function testFetchValueNotFound(): void
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn(null);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with('system.foo');

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(SystemParameterEntity::class)
            ->andReturn($repo);

        $result = $this->sut->fetchValue('system.foo');

        $this->assertNull($result);
    }

    public function testFetchValueNotFoundException(): void
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andThrow(NotFoundException::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with('system.foo');

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(SystemParameterEntity::class)
            ->andReturn($repo);

        $result = $this->sut->fetchValue('system.foo');

        $this->assertNull($result);
    }

    public function testFetchValue(): void
    {
        $spe = new SystemParameterEntity();
        $spe->setParamValue('VALUE');
        $results = [$spe];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with('system.foo');

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(SystemParameterEntity::class)
            ->andReturn($repo);

        $this->assertSame('VALUE', $this->sut->fetchValue('system.foo'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProvider')]
    public function testGetDisableSelfServeCardPayments(mixed $expected, mixed $value): void
    {
        $this->setupFetchValue(SystemParameterEntity::DISABLED_SELFSERVE_CARD_PAYMENTS, $value);
        $this->assertSame($expected, $this->sut->getDisableSelfServeCardPayments());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProvider')]
    public function testIsSelfservePromptEnabled(mixed $expected, mixed $value): void
    {
        $this->setupFetchValue(SystemParameterEntity::ENABLE_SELFSERVE_PROMPT, $value);
        $this->assertSame($expected, $this->sut->isSelfservePromptEnabled());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProvider')]
    public function testGetDisabledDigitalContinuations(mixed $expected, mixed $value): void
    {
        $this->setupFetchValue(SystemParameterEntity::DISABLE_DIGITAL_CONTINUATIONS, $value);
        $this->assertSame($expected, $this->sut->getDisabledDigitalContinuations());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProviderDeletes')]
    public function testGetDisableDataRetentionDocumentDelete(mixed $expected, mixed $value): void
    {
        $this->setupFetchValue(SystemParameterEntity::DISABLE_DATA_RETENTION_DOCUMENT_DELETE, $value);
        $this->assertSame($expected, $this->sut->getDisableDataRetentionDocumentDelete());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProviderDeletes')]
    public function testGetDisableDataRetentionDelete(mixed $expected, mixed $value): void
    {
        $this->setupFetchValue(SystemParameterEntity::DISABLE_DATA_RETENTION_DELETE, $value);
        $this->assertSame($expected, $this->sut->getDisableDataRetentionDelete());
    }

    public static function boolDataProvider(): \Iterator
    {
        yield [true, true];
        yield [false, false];
        yield [false, 0];
        yield [true, 1];
        yield [false, '0'];
        yield [true, '1'];
        yield [false, null];
        yield [false, ''];
        yield [true, 'X'];
    }

    public static function boolDataProviderDeletes(): \Iterator
    {
        yield [true, true];
        yield [false, false];
        yield [false, 0];
        yield [true, 1];
        yield [false, '0'];
        yield [true, '1'];
        yield [true, null];
        yield [false, ''];
        yield [true, 'X'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestGetDigitalContinuationReminderPeriod')]
    public function testGetDigitalContinuationReminderPeriod(mixed $expected, mixed $value): void
    {
        $this->setupFetchValue(SystemParameterEntity::DIGITAL_CONTINUATION_REMINDER_PERIOD, $value);
        $this->assertSame($expected, $this->sut->getDigitalContinuationReminderPeriod());
    }

    public static function dataProviderTestGetDigitalContinuationReminderPeriod(): \Iterator
    {
        yield [20, 20];
        yield [1, '1'];
        yield [99, '99'];
        yield [SystemParameterRepo::DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT, 'X'];
        yield [SystemParameterRepo::DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT, ''];
        yield [SystemParameterRepo::DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT, null];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestGetSystemDataRetentionUser')]
    public function testGetSystemDataRetentionUser(mixed $expected, mixed $value): void
    {
        $this->setupFetchValue(SystemParameterEntity::SYSTEM_DATA_RETENTION_USER, $value);

        if ($expected === 'EXCEPTION') {
            $this->expectException(
                RuntimeException::class
            );
            $this->sut->getSystemDataRetentionUser();
        } else {
            $this->assertSame($expected, $this->sut->getSystemDataRetentionUser());
        }
    }

    public static function dataProviderTestGetSystemDataRetentionUser(): \Iterator
    {
        yield [20, 20];
        yield [1, '1'];
        yield [99, '99'];
        yield ['EXCEPTION', 'X'];
        yield ['EXCEPTION', null];
        yield ['EXCEPTION', 0];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestGetDataRetentionDeleteLimit')]
    public function testGetDataRetentionDeleteLimit(mixed $expected, mixed $value): void
    {
        $this->setupFetchValue(SystemParameterEntity::DR_DELETE_LIMIT, $value);

        $this->assertSame($expected, $this->sut->getDataRetentionDeleteLimit());
    }

    public static function dataProviderTestGetDataRetentionDeleteLimit(): \Iterator
    {
        yield [20, 20];
        yield [1, '1'];
        yield [99, '99'];
        yield [0, 'X'];
        yield [0, null];
        yield [0, 0];
    }

    /**
     * Setup a system parameter to return a value
     *
     * @param string $name  System parameter name (SystemParameter:: constant)
     * @param string $value Value for the system parameter
     *
     * @return void
     */
    private function setupFetchValue(mixed $name, mixed $value): void
    {
        $spe = new SystemParameterEntity();
        $spe->setParamValue($value);
        $results = [$spe];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with($name);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(SystemParameterEntity::class)
            ->andReturn($repo);
    }
}
