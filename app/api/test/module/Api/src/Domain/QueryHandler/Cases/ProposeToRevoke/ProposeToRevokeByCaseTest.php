<?php

declare(strict_types=1);

/**
 * ProposeToRevokeByCase Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\ProposeToRevoke;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\ProposeToRevoke\ProposeToRevokeByCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\ProposeToRevoke as ProposeToRevokeRepo;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate;
use Dvsa\Olcs\Transfer\Query\Cases\ProposeToRevoke\ProposeToRevokeByCase as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * ProposeToRevokeByCase Test
 */
class ProposeToRevokeByCaseTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProposeToRevokeByCase();
        $this->mockRepo('ProposeToRevoke', ProposeToRevokeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['case' => 1]);

        $this->repoMap['ProposeToRevoke']
            ->shouldReceive('disableSoftDeleteable')
            ->with(
                [
                    \Dvsa\Olcs\Api\Entity\Pi\Reason::class
                ]
            )
            ->once();

        $proposeToRevokeEntity = m::mock(ProposeToRevoke::class);

        $this->repoMap['ProposeToRevoke']
            ->shouldReceive('fetchProposeToRevokeUsingCase')
            ->with($query)
            ->andReturn($proposeToRevokeEntity);

        $proposeToRevokeEntity
            ->shouldReceive('getSlaTargetDates')
            ->andReturn(
                [
                    $this->createSlaTargetDate('dummySlaProperty1', 'DUMMY-SLA-VAL-1'),
                    $this->createSlaTargetDate('dummySlaProperty2', 'DUMMY-SLA-VAL-2'),
                ]
            );

        $proposeToRevokeEntity
            ->shouldReceive('serialize')
            ->with(
                [
                    'presidingTc',
                    'reasons',
                    'assignedCaseworker',
                    'actionToBeTaken',
                    'approvalSubmissionPresidingTc',
                    'finalSubmissionPresidingTc'
                ]
            )
            ->andReturn(['foo']);

        /** @var Result $result */
        $result = $this->sut->handleQuery($query);
        $this->assertSame(
            [
                'foo',
                'dummySlaProperty1Target' => 'DUMMY-SLA-VAL-1',
                'dummySlaProperty2Target' => 'DUMMY-SLA-VAL-2',
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryWhenPtrNotFound(): void
    {
        $query = Qry::create(['case' => 1]);

        $this->repoMap['ProposeToRevoke']
            ->shouldReceive('disableSoftDeleteable')
            ->with(
                [
                    \Dvsa\Olcs\Api\Entity\Pi\Reason::class
                ]
            )
            ->once();

        $this->repoMap['ProposeToRevoke']
            ->shouldReceive('fetchProposeToRevokeUsingCase')
            ->with($query)
            ->andReturn(null);

        $expected = new Result(
            null,
            [
                'presidingTc',
                'reasons',
                'assignedCaseworker',
                'actionToBeTaken',
                'approvalSubmissionPresidingTc',
                'finalSubmissionPresidingTc'
            ],
            []
        );
        $this->assertSame((array)$expected, (array)$this->sut->handleQuery($query));
    }

    protected function createSlaTargetDate(mixed $name, mixed $targetDate): mixed
    {
        $mock = m::mock(SlaTargetDate::class);
        $mock->shouldReceive('getSla->getField')->andReturn($name);
        $mock->shouldReceive('getTargetDate')->andReturn($targetDate);
        return $mock;
    }
}
