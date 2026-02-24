<?php

declare(strict_types=1);

/**
 * Grant Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Grant as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Query\Application\Grant;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Grant Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', ApplicationRepo::class);

        $this->mockedSmServices['ApplicationGrantValidationService'] = m::mock();

        parent::setUp();
    }

    public function testHandleCommandOppositionNotPassed(): void
    {
        $query = Grant::create(['id' => 111]);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->shouldReceive('serialize')->with([])->andReturn(['foo' => 'bar']);
        $application->shouldReceive('isVariation')->with()->andReturn(false);
        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);

        $this->mockedSmServices['ApplicationGrantValidationService']->shouldReceive('validate')->with($application)
            ->andReturn([]);

        $result = $this->sut->handleQuery($query)->serialize();

        $expected = [
            'foo' => 'bar',
            'canGrant' => true,
            'reasons' => [],
            'canHaveInspectionRequest' => true
        ];

        $this->assertSame($expected, $result);
    }

    public function testHandleCommandValidationError(): void
    {
        $query = Grant::create(['id' => 111]);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->shouldReceive('serialize')->with([])->andReturn(['foo' => 'bar']);
        $application->shouldReceive('isVariation')->with()->andReturn(true);
        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->andReturn($application);

        $this->mockedSmServices['ApplicationGrantValidationService']->shouldReceive('validate')->with($application)
            ->andReturn(['FAILED']);

        $result = $this->sut->handleQuery($query)->serialize();

        $expected = [
            'foo' => 'bar',
            'canGrant' => false,
            'reasons' => ['FAILED'],
            'canHaveInspectionRequest' => false
        ];

        $this->assertSame($expected, $result);
    }
}
