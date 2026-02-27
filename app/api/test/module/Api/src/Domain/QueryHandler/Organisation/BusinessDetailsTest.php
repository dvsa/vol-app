<?php

declare(strict_types=1);

/**
 * Business Details Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\BusinessDetails;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Transfer\Query\Organisation\BusinessDetails as Qry;

/**
 * Business Details Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusinessDetails();
        $this->mockRepo('Organisation', OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $this->repoMap['Organisation']->shouldReceive('fetchBusinessDetailsUsingId')
            ->with($query)
            ->andReturn(['id' => 111]);

        $this->assertEquals(['id' => 111], $this->sut->handleQuery($query));
    }
}
