<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Pi;

use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi\SlaExceptionList;
use Dvsa\Olcs\Api\Domain\Repository\SlaException as SlaExceptionRepo;
use Dvsa\Olcs\Api\Entity\Pi\SlaException as SlaExceptionEntity;
use Dvsa\Olcs\Transfer\Query\Cases\Pi\SlaExceptionList as SlaExceptionListQuery;
use Mockery as m;

/**
 * SLA Exception List Query Handler Test
 *
 * @covers \Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi\SlaExceptionList
 */
class SlaExceptionListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new SlaExceptionList();
        $this->mockRepo('SlaException', SlaExceptionRepo::class);

        parent::setUp();
    }

    /**
     * Test successful query execution with multiple SLA exceptions
     */
    public function testHandleQueryWithMultipleResults()
    {
        // Create mock SLA exceptions
        $slaException1 = m::mock(SlaExceptionEntity::class);
        $slaException1->shouldReceive('getId')->andReturn(1);
        $slaException1->shouldReceive('getSlaDescription')->andReturn('Standard processing time');
        $slaException1->shouldReceive('getSlaExceptionDescription')->andReturn('Exception for complex cases');
        $slaException1->shouldReceive('getEffectiveFrom')->andReturn(new \DateTime('2024-01-01'));
        $slaException1->shouldReceive('getEffectiveTo')->andReturn(new \DateTime('2024-12-31'));

        $slaException2 = m::mock(SlaExceptionEntity::class);
        $slaException2->shouldReceive('getId')->andReturn(2);
        $slaException2->shouldReceive('getSlaDescription')->andReturn('Fast track processing');
        $slaException2->shouldReceive('getSlaExceptionDescription')->andReturn('Emergency exception');
        $slaException2->shouldReceive('getEffectiveFrom')->andReturn(new \DateTime('2024-06-01'));
        $slaException2->shouldReceive('getEffectiveTo')->andReturn(null);

        $mockExceptions = [$slaException1, $slaException2];

        // Mock repository call
        $this->repoMap['SlaException']
            ->shouldReceive('fetchActive')
            ->andReturn($mockExceptions)
            ->once();

        // Create and execute query
        $query = SlaExceptionListQuery::create([]);
        $result = $this->sut->handleQuery($query);

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('results', $result);

        $this->assertEquals(2, $result['count']);
        $this->assertCount(2, $result['result']);
        $this->assertEquals($result['result'], $result['results']); // For compatibility

        // Check first result
        $firstResult = $result['result'][0];
        $this->assertEquals(1, $firstResult['id']);
        $this->assertEquals('Standard processing time', $firstResult['slaDescription']);
        $this->assertEquals('Exception for complex cases', $firstResult['slaExceptionDescription']);
        $this->assertEquals('2024-01-01', $firstResult['effectiveFrom']);
        $this->assertEquals('2024-12-31', $firstResult['effectiveTo']);

        // Check second result
        $secondResult = $result['result'][1];
        $this->assertEquals(2, $secondResult['id']);
        $this->assertEquals('Fast track processing', $secondResult['slaDescription']);
        $this->assertEquals('Emergency exception', $secondResult['slaExceptionDescription']);
        $this->assertEquals('2024-06-01', $secondResult['effectiveFrom']);
        $this->assertNull($secondResult['effectiveTo']);
    }

    /**
     * Test query execution with no results
     */
    public function testHandleQueryWithNoResults()
    {
        // Mock repository call returning empty array
        $this->repoMap['SlaException']
            ->shouldReceive('fetchActive')
            ->andReturn([])
            ->once();

        // Create and execute query
        $query = SlaExceptionListQuery::create([]);
        $result = $this->sut->handleQuery($query);

        // Assertions
        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('results', $result);

        $this->assertEquals(0, $result['count']);
        $this->assertCount(0, $result['result']);
        $this->assertEquals([], $result['result']);
        $this->assertEquals($result['result'], $result['results']);
    }

    /**
     * Test date formatting when dates are strings
     */
    public function testHandleQueryWithStringDates()
    {
        // Create mock SLA exception with string dates
        $slaException = m::mock(SlaExceptionEntity::class);
        $slaException->shouldReceive('getId')->andReturn(1);
        $slaException->shouldReceive('getSlaDescription')->andReturn('Test SLA');
        $slaException->shouldReceive('getSlaExceptionDescription')->andReturn('Test Exception');
        $slaException->shouldReceive('getEffectiveFrom')->andReturn('2024-01-01'); // String date
        $slaException->shouldReceive('getEffectiveTo')->andReturn('2024-12-31'); // String date

        $mockExceptions = [$slaException];

        // Mock repository call
        $this->repoMap['SlaException']
            ->shouldReceive('fetchActive')
            ->andReturn($mockExceptions)
            ->once();

        // Create and execute query
        $query = SlaExceptionListQuery::create([]);
        $result = $this->sut->handleQuery($query);

        // Assertions
        $this->assertEquals(1, $result['count']);
        $firstResult = $result['result'][0];
        $this->assertEquals('2024-01-01', $firstResult['effectiveFrom']);
        $this->assertEquals('2024-12-31', $firstResult['effectiveTo']);
    }

    /**
     * Test query handler repository configuration
     */
    public function testRepositoryConfiguration()
    {
        $reflection = new \ReflectionClass($this->sut);
        
        $repoServiceNameProperty = $reflection->getProperty('repoServiceName');
        $repoServiceNameProperty->setAccessible(true);
        $this->assertEquals('SlaException', $repoServiceNameProperty->getValue($this->sut));
    }

    /**
     * Test single SLA exception result
     */
    public function testHandleQueryWithSingleResult()
    {
        // Create mock SLA exception
        $slaException = m::mock(SlaExceptionEntity::class);
        $slaException->shouldReceive('getId')->andReturn(5);
        $slaException->shouldReceive('getSlaDescription')->andReturn('Weekly reporting');
        $slaException->shouldReceive('getSlaExceptionDescription')->andReturn('Holiday exception');
        $slaException->shouldReceive('getEffectiveFrom')->andReturn(new \DateTime('2024-01-15'));
        $slaException->shouldReceive('getEffectiveTo')->andReturn(new \DateTime('2024-01-22'));

        $mockExceptions = [$slaException];

        // Mock repository call
        $this->repoMap['SlaException']
            ->shouldReceive('fetchActive')
            ->andReturn($mockExceptions)
            ->once();

        // Create and execute query
        $query = SlaExceptionListQuery::create([]);
        $result = $this->sut->handleQuery($query);

        // Assertions
        $this->assertEquals(1, $result['count']);
        $this->assertCount(1, $result['result']);
        
        $singleResult = $result['result'][0];
        $this->assertEquals(5, $singleResult['id']);
        $this->assertEquals('Weekly reporting', $singleResult['slaDescription']);
        $this->assertEquals('Holiday exception', $singleResult['slaExceptionDescription']);
        $this->assertEquals('2024-01-15', $singleResult['effectiveFrom']);
        $this->assertEquals('2024-01-22', $singleResult['effectiveTo']);
    }

    /**
     * Test that result array structure is consistent
     */
    public function testResultStructure()
    {
        // Mock empty result
        $this->repoMap['SlaException']
            ->shouldReceive('fetchActive')
            ->andReturn([])
            ->once();

        $query = SlaExceptionListQuery::create([]);
        $result = $this->sut->handleQuery($query);

        // Test required keys exist
        $expectedKeys = ['result', 'count', 'results'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Result should contain key: {$key}");
        }

        // Test result and results are the same (for compatibility)
        $this->assertSame($result['result'], $result['results']);
    }
}
