<?php

declare(strict_types=1);

/**
 * CasesWithLicence test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\CasesWithLicence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as Qry;
use Mockery as m;

/**
 * CasesWithLicence test
 */
class CasesWithLicenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CasesWithLicence();
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 24]);

        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);

        $this->repoMap['Cases']->shouldReceive('fetchWithLicenceUsingId')
            ->with($query)
            ->andReturn($mockResult);

        $result = $this->sut->handleQuery($query);
        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class, $result);
    }
}
