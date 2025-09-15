<?php

/**
 * PiSlaException Repository Test
 *
 * @author Generated
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\PiSlaException as Repo;
use Dvsa\Olcs\Api\Entity\Pi\PiSlaException as Entity;

/**
 * PiSlaException Repository Test
 *
 * @author Generated
 */
class PiSlaExceptionTest extends RepositoryTestCase
{
    /**
     * @var Repo
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }


    public function testFetchByPi()
    {
        $piId = 123;
        
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULT1', 'RESULT2'])
                ->getMock()
        );

        $result = $this->sut->fetchByPi($piId);
        
        $this->assertEquals(['RESULT1', 'RESULT2'], $result);

        $expectedQuery = 'BLAH ' .
            'AND m.pi = [[123]] ' .
            'INNER JOIN m.slaException se ' .
            'SELECT se ' .
            'ORDER BY se.slaDescription ASC ' .
            'ORDER BY m.createdOn DESC';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByCase()
    {
        $caseId = 456;
        
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['CASE_RESULT'])
                ->getMock()
        );

        $result = $this->sut->fetchByCase($caseId);
        
        $this->assertEquals(['CASE_RESULT'], $result);

        $expectedQuery = 'BLAH ' .
            'INNER JOIN m.pi p ' .
            'AND p.case = [[456]] ' .
            'INNER JOIN m.slaException se ' .
            'SELECT se, p ' .
            'ORDER BY se.slaDescription ASC ' .
            'ORDER BY m.createdOn DESC';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchActiveByPi()
    {
        $piId = 789;
        $checkDate = new \DateTime('2024-01-15');
        
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['ACTIVE_RESULT'])
                ->getMock()
        );

        $result = $this->sut->fetchActiveByPi($piId, $checkDate);
        
        $this->assertEquals(['ACTIVE_RESULT'], $result);

        $expectedQuery = 'BLAH ' .
            'AND m.pi = [[789]] ' .
            'INNER JOIN m.slaException se ' .
            'AND se.effectiveFrom <= [[2024-01-15T00:00:00+00:00]] ' .
            'AND (se.effectiveTo IS NULL OR se.effectiveTo >= [[2024-01-15T00:00:00+00:00]]) ' .
            'SELECT se ' .
            'ORDER BY se.slaDescription ASC';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchActiveByPiWithDefaultDate()
    {
        $piId = 999;
        // Not passing checkDate parameter - should use current date
        
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['ACTIVE_DEFAULT_RESULT'])
                ->getMock()
        );

        $result = $this->sut->fetchActiveByPi($piId);
        
        $this->assertEquals(['ACTIVE_DEFAULT_RESULT'], $result);

        // Check that query contains today's date and proper structure
        $today = (new \DateTime())->format('Y-m-d');
        $this->assertStringContainsString('AND m.pi = [[999]]', $this->query);
        $this->assertStringContainsString('INNER JOIN m.slaException se', $this->query);
        $this->assertStringContainsString('se.effectiveFrom <=', $this->query);
        $this->assertStringContainsString($today, $this->query);
        $this->assertStringContainsString('ORDER BY se.slaDescription ASC', $this->query);
    }

    public function testFetchByPiWithEmptyResult()
    {
        $piId = 888;
        
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([])
                ->getMock()
        );

        $result = $this->sut->fetchByPi($piId);
        
        $this->assertEquals([], $result);

        $expectedQuery = 'BLAH ' .
            'AND m.pi = [[888]] ' .
            'INNER JOIN m.slaException se ' .
            'SELECT se ' .
            'ORDER BY se.slaDescription ASC ' .
            'ORDER BY m.createdOn DESC';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByCaseWithEmptyResult()
    {
        $caseId = 777;
        
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);
        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([])
                ->getMock()
        );

        $result = $this->sut->fetchByCase($caseId);
        
        $this->assertEquals([], $result);

        $expectedQuery = 'BLAH ' .
            'INNER JOIN m.pi p ' .
            'AND p.case = [[777]] ' .
            'INNER JOIN m.slaException se ' .
            'SELECT se, p ' .
            'ORDER BY se.slaDescription ASC ' .
            'ORDER BY m.createdOn DESC';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
