<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\QueryHandler\CommunityLic\CommunityLicence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicence as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Mockery as m;

/**
 * Community Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommunityLicence();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockCommunityLic = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['id' => 111])
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockCommunityLic);

        $this->assertEquals(['id' => 111], $this->sut->handleQuery($query)->serialize());
    }
}
