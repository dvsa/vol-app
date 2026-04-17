<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Messaging\Conversations;

use ArrayIterator;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Conversations\ByOrganisation;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByOrganisation as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class ByOrganisationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ByOrganisation();
        $this->mockRepo(Repository\Conversation::class, Repository\Conversation::class);
        $this->mockRepo(Repository\Message::class, Repository\Message::class);

        $this->mockedSmServices = [
            'SectionAccessService' => m::mock(),
            AuthorizationService::class => m::mock(AuthorizationService::class)
                ->shouldReceive('isGranted')
                ->with(Permission::SELFSERVE_USER, null)
                ->andReturn(true)
                ->shouldReceive('isGranted')
                ->with(Permission::INTERNAL_USER, null)
                ->andReturn(false)
                ->getMock(),
        ];

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser->isInternal')
            ->once()
            ->andReturn(false);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([
            'organisation' => 1,
        ]);

        $conversations = new ArrayIterator(
            [
                ['has_unread' => false, 0 => ['id' => 1, 'isClosed' => false]],
                ['has_unread' => false, 0 => ['id' => 2, 'isClosed' => true]],
            ]
        );
        $mockQb = m::mock(QueryBuilder::class);
        $this->repoMap[Repository\Conversation::class]
            ->shouldReceive('getByOrganisationId')
            ->once()
            ->andReturn($mockQb);
        $this->repoMap[Repository\Conversation::class]
            ->shouldReceive('filterByStatuses')
            ->once()
            ->with($mockQb, [])->andReturn($mockQb);
        $this->repoMap[Repository\Conversation::class]
            ->shouldReceive('applyOrderForListing')
            ->once()
            ->with($mockQb, ['operator-tc', 'operator-admin', 'operator-user'])
            ->andReturn($mockQb);
        $this->repoMap[Repository\Conversation::class]
            ->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, AbstractQuery::HYDRATE_ARRAY, $query)
            ->andReturn($conversations);
        $this->repoMap[Repository\Conversation::class]
            ->shouldReceive('fetchPaginatedCount')
            ->once()
            ->with($mockQb)
            ->andReturn(10);
        $this->repoMap[Repository\Message::class]
            ->shouldReceive('getLastMessageForConversation')
            ->twice()
            ->andReturn($conversations[0]);

        $result = $this->sut->handleQuery($query);

        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertCount(2, $result['result']);
        $this->assertEquals(10, $result['count']);
    }
}
