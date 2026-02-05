<?php

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
class SystemParameterTest extends RepositoryTestCase
{
    /** @var  SystemParameterRepo */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(SystemParameterRepo::class);
    }

    public function testFetchValueNotFound()
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

    public function testFetchValueNotFoundException()
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

    public function testFetchValue()
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
    public function testGetDisableSelfServeCardPayments($expected, $value)
    {
        $this->setupFetchValue(SystemParameterEntity::DISABLED_SELFSERVE_CARD_PAYMENTS, $value);
        $this->assertSame($expected, $this->sut->getDisableSelfServeCardPayments());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProvider')]
    public function testIsSelfservePromptEnabled($expected, $value)
    {
        $this->setupFetchValue(SystemParameterEntity::ENABLE_SELFSERVE_PROMPT, $value);
        $this->assertSame($expected, $this->sut->isSelfservePromptEnabled());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProvider')]
    public function testGetDisabledDigitalContinuations($expected, $value)
    {
        $this->setupFetchValue(SystemParameterEntity::DISABLE_DIGITAL_CONTINUATIONS, $value);
        $this->assertSame($expected, $this->sut->getDisabledDigitalContinuations());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProviderDeletes')]
    public function testGetDisableDataRetentionDocumentDelete($expected, $value)
    {
        $this->setupFetchValue(SystemParameterEntity::DISABLE_DATA_RETENTION_DOCUMENT_DELETE, $value);
        $this->assertSame($expected, $this->sut->getDisableDataRetentionDocumentDelete());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('boolDataProviderDeletes')]
    public function testGetDisableDataRetentionDelete($expected, $value)
    {
        $this->setupFetchValue(SystemParameterEntity::DISABLE_DATA_RETENTION_DELETE, $value);
        $this->assertSame($expected, $this->sut->getDisableDataRetentionDelete());
    }

    public static function boolDataProvider()
    {
        return [
            [true, true],
            [false, false],
            [false, 0],
            [true, 1],
            [false, '0'],
            [true, '1'],
            [false, null],
            [false, ''],
            [true, 'X'],
        ];
    }

    public static function boolDataProviderDeletes()
    {
        return [
            [true, true],
            [false, false],
            [false, 0],
            [true, 1],
            [false, '0'],
            [true, '1'],
            [true, null],
            [false, ''],
            [true, 'X'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestGetDigitalContinuationReminderPeriod')]
    public function testGetDigitalContinuationReminderPeriod($expected, $value)
    {
        $this->setupFetchValue(SystemParameterEntity::DIGITAL_CONTINUATION_REMINDER_PERIOD, $value);
        $this->assertSame($expected, $this->sut->getDigitalContinuationReminderPeriod());
    }

    public static function dataProviderTestGetDigitalContinuationReminderPeriod()
    {
        return [
            [20, 20],
            [1, '1'],
            [99, '99'],
            [SystemParameterRepo::DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT, 'X'],
            [SystemParameterRepo::DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT, ''],
            [SystemParameterRepo::DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT, null],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestGetSystemDataRetentionUser')]
    public function testGetSystemDataRetentionUser($expected, $value)
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

    public static function dataProviderTestGetSystemDataRetentionUser()
    {
        return [
            [20, 20],
            [1, '1'],
            [99, '99'],
            ['EXCEPTION', 'X'],
            ['EXCEPTION', null],
            ['EXCEPTION', 0],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestGetDataRetentionDeleteLimit')]
    public function testGetDataRetentionDeleteLimit($expected, $value)
    {
        $this->setupFetchValue(SystemParameterEntity::DR_DELETE_LIMIT, $value);

        $this->assertSame($expected, $this->sut->getDataRetentionDeleteLimit());
    }

    public static function dataProviderTestGetDataRetentionDeleteLimit()
    {
        return [
            [20, 20],
            [1, '1'],
            [99, '99'],
            [0, 'X'],
            [0, null],
            [0, 0],
        ];
    }

    /**
     * Setup a system parameter to return a value
     *
     * @param string $name  System parameter name (SystemParameter:: constant)
     * @param string $value Value for the system parameter
     *
     * @return void
     */
    private function setupFetchValue($name, $value)
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
