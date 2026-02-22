<?php

declare(strict_types=1);

/**
 * CpidOrganisationTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Organisation;

use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\CpidOrganisation;
use Dvsa\Olcs\Api\Domain\Repository\Organisation;
use Dvsa\Olcs\Transfer\Query\Organisation\CpidOrganisation as Qry;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * CpidOrganisationExportTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class CpidOrganisationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CpidOrganisation();
        $this->mockRepo('Organisation', Organisation::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['cpid' => 1]);

        $mockCpid = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap['Organisation']
            ->shouldReceive('fetchByStatusPaginated')
            ->with($query)
            ->andReturn(
                [
                    'count' => 1,
                    'result' => [$mockCpid]
                ]
            )
            ->getMock();

        $this->assertEquals(
            $this->sut->handleQuery($query),
            [
                'result' => [['foo' => 'bar']],
                'count' => 1
            ]
        );
    }
}
