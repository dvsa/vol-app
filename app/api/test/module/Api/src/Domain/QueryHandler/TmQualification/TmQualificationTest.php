<?php

declare(strict_types=1);

/**
 * TmQualification Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TmQualification;

use Dvsa\Olcs\Api\Domain\QueryHandler\TmQualification\TmQualification as QueryHandler;
use Dvsa\Olcs\Transfer\Query\TmQualification\TmQualification as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\TmQualification as TmQualificationRepo;

/**
 * TmQualification Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmQualificationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TmQualification', TmQualificationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $bundle = [
            'qualificationType',
            'countryCode',
            'transportManager'
        ];
        $query = Query::create(['id' => 1]);

        $mock = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')->with($bundle)
            ->once()
            ->andReturn(['foo'])
            ->getMock();

        $this->repoMap['TmQualification']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mock);

        $this->assertSame(['foo'], $this->sut->handleQuery($query)->serialize());
    }
}
