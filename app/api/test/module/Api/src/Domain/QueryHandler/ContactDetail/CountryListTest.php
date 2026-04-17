<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ContactDetail;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\ContactDetail\CountryList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Country as Repo;
use Dvsa\Olcs\Transfer\Query\ContactDetail\CountryList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;

/**
 * Get country list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CountryListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Country', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['QUERY']);

        $country = m::mock(CountryEntity::class);
        $country->shouldReceive('serialize')->once()->andReturn('SERIALIZED');

        $this->repoMap['Country']->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->andReturn([$country]);
        $this->repoMap['Country']->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result['result']);
        $this->assertSame('COUNT', $result['count']);
    }
}
