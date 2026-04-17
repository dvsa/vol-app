<?php

declare(strict_types=1);

/**
 * Other Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\QueryHandler\OtherLicence\OtherLicence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\OtherLicence\OtherLicence as Qry;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence as OtherLicenceRepo;
use Doctrine\ORM\Query;
use Mockery as m;

/**
 * Other Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OtherLicenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new OtherLicence();
        $this->mockRepo('OtherLicence', OtherLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockOtherLicence = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $mockOtherLicence->shouldReceive('serialize')
            ->once()
            ->andReturn(['id' => 111])
            ->once();

        $this->repoMap['OtherLicence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockOtherLicence)
            ->once();

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result->serialize(), ['id' => 111]);
    }
}
