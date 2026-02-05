<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ContactDetail;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\ContactDetail\ContactDetailsList;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as Repo;
use Dvsa\Olcs\Transfer\Query\ContactDetail\ContactDetailsList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;

/**
 * Get country list test
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\ContactDetail\ContactDetailsList::class)]
class ContactDetailsListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ContactDetailsList();
        $this->mockRepo('ContactDetails', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(
            [
                'limit' => 999,
            ]
        );

        $mockCdEntity = m::mock(ContactDetailsEntity::class);
        $mockCdEntity->shouldReceive('serialize')->once()->andReturn('SERIALIZED');

        $this->repoMap['ContactDetails']
            ->shouldReceive('fetchList')->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->andReturn([$mockCdEntity])
            ->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result['result']);
        $this->assertSame('COUNT', $result['count']);
    }
}
