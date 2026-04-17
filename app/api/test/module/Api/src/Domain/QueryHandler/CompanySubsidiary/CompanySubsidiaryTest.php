<?php

declare(strict_types=1);

/**
 * Company Subsidiary Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\CompanySubsidiary;

use Dvsa\Olcs\Api\Domain\QueryHandler\CompanySubsidiary\CompanySubsidiary;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\CompanySubsidiary as CompanySubsidiaryRepo;
use Dvsa\Olcs\Transfer\Query\CompanySubsidiary\CompanySubsidiary as Qry;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary as Entity;

/**
 * Company Subsidiary Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CompanySubsidiaryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CompanySubsidiary();
        $this->mockRepo('CompanySubsidiary', CompanySubsidiaryRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);
        $expected = ['foo'];

        $mockResult = m::mock(Entity::class);

        $this->repoMap['CompanySubsidiary']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockResult);

        $result = $this->sut->handleQuery($query);

        $mockResult
            ->shouldReceive('serialize')
            ->once()
            ->andReturn($expected);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->serialize());
    }
}
