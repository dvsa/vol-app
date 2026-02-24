<?php

declare(strict_types=1);

/**
 * Operator Business Details Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Operator;

use Dvsa\Olcs\Api\Domain\QueryHandler\Operator\BusinessDetails as BusinessDetailsQueryHanlder;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\Operator\BusinessDetails as Qry;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Mockery as m;

/**
 * Operator Business Details Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusinessDetailsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(BusinessDetailsQueryHanlder::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->mockRepo('Organisation', OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $this->repoMap['Organisation']->shouldReceive('fetchBusinessDetailsUsingId')
            ->with($query)
            ->andReturn('organisation')
            ->once();

        $this->sut->shouldReceive('result')
            ->with(
                'organisation',
                [
                    'organisationPersons' => ['person'],
                    'contactDetails' => ['address']
                ]
            )
            ->andReturn('result')
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result, 'result');
    }
}
