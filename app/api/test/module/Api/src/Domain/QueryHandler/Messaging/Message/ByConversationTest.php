<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Messaging\Message;

use ArrayIterator;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Message\ByConversation;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingUserMessageRead;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\ByConversation as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

class ByConversationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ByConversation();
        $this->mockRepo(Repository\Conversation::class, Repository\Conversation::class);
        $this->mockRepo(Repository\Message::class, Repository\Message::class);
        $this->mockRepo(Repository\MessagingUserMessageRead::class, Repository\MessagingUserMessageRead::class);

        $this->mockedSmServices = ['SectionAccessService' => m::mock(), AuthorizationService::class => m::mock(AuthorizationService::class)->shouldReceive('isGranted')->with(Permission::SELFSERVE_USER, null)->andReturn(true)->shouldReceive('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(false)->getMock(),];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser->getId')->andReturn(1);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([
            'conversation' => 1,
            'includeReadRoles' => true,
            'readRoles' => [Role::ROLE_OPERATOR_USER],
        ]);

        $messages = new ArrayIterator(
            [
                ['id' => 1, 'messaging_conversation_id' => '1', 'messaging_content_id' => '1', 'userMessageReads' => [['user' => ['roles' => [['role' => Role::ROLE_OPERATOR_USER]]]]]],
                ['id' => 2, 'messaging_conversation_id' => '1', 'messaging_content_id' => '2', 'userMessageReads' => [['user' => ['roles' => [['role' => Role::ROLE_OPERATOR_TM]]]]]],
                ['id' => 3, 'messaging_conversation_id' => '1', 'messaging_content_id' => '3', 'userMessageReads' => [['user' => ['roles' => []]]]],
                ['id' => 4, 'messaging_conversation_id' => '1', 'messaging_content_id' => '4', 'userMessageReads' => [['user' => ['roles' => []]]]],
                ['id' => 5, 'messaging_conversation_id' => '1', 'messaging_content_id' => '5', 'userMessageReads' => [['user' => ['roles' => []]]]],
                ['id' => 6, 'messaging_conversation_id' => '1', 'messaging_content_id' => '6', 'userMessageReads' => [['user' => ['roles' => []]]]],
                ['id' => 7, 'messaging_conversation_id' => '1', 'messaging_content_id' => '7', 'userMessageReads' => [['user' => ['roles' => []]]]],
                ['id' => 8, 'messaging_conversation_id' => '1', 'messaging_content_id' => '8', 'userMessageReads' => [['user' => ['roles' => []]]]],
            ],
        );
        $conversation = new MessagingConversation();
        $mockQb = m::mock(QueryBuilder::class);
        $this->repoMap[Repository\Message::class]
            ->shouldReceive('getBaseMessageListWithContentQuery')
            ->once()
            ->andReturn($mockQb);
        $this->repoMap[Repository\Message::class]
            ->shouldReceive('filterByConversationId')
            ->once()
            ->andReturn($mockQb);
        $this->repoMap[Repository\Message::class]
            ->shouldReceive('addReadersToMessages')
            ->once()
            ->andReturn($mockQb);
        $this->repoMap[Repository\Message::class]
            ->shouldReceive('fetchPaginatedList')
            ->with($mockQb, Query::HYDRATE_ARRAY, $query)
            ->once()
            ->andReturn($messages);
        $this->repoMap[Repository\Message::class]
            ->shouldReceive('fetchPaginatedCount')
            ->with($mockQb)
            ->once()
            ->andReturn(10);
        $this->repoMap[Repository\Conversation::class]
            ->shouldReceive('fetchById')
            ->with(1)
            ->once()
            ->andReturn($conversation);

        foreach ($messages as $message) {
            $this->repoMap[Repository\Message::class]
                ->shouldReceive('fetchById')
                ->with($message['id'])
                ->once()
                ->andReturn($message);
        }

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('serialize')
                    ->once()
                    ->andReturn(['id' => 123]);

        $mockApplication = m::mock(Application::class);
        $mockApplication->shouldReceive('serialize')
                        ->once()
                        ->andReturn(['id' => 456]);

        $mockTask = m::mock(Task::class);
        $mockTask->shouldReceive('getLicence')
                 ->once()
                 ->andReturn($mockLicence);
        $mockTask->shouldReceive('getApplication')
                 ->once()
                 ->andReturn($mockApplication);
        $conversation->setTask($mockTask);

        $mockUserMessageRead = m::mock(MessagingUserMessageRead::class);
        $mockUserMessageRead->shouldReceive('setLastReadOn')->times(count($messages));
        $this->repoMap[Repository\MessagingUserMessageRead::class]->shouldReceive('fetchByMessageIdAndUserId')->times(count($messages))->andReturn($mockUserMessageRead);
        $this->repoMap[Repository\MessagingUserMessageRead::class]->shouldReceive('save')->times(count($messages));

        $result = $this->sut->handleQuery($query);

        $this->assertArrayHasKey('application', $result);
        $this->assertArrayHasKey('licence', $result);
        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertCount(8, $result['result']);
        $this->assertEquals(10, $result['count']);
        $this->assertEquals(456, $result['application']['id']);
        $this->assertEquals(123, $result['licence']['id']);
        $this->assertNotEmpty($result['result'][0]['userMessageReads']);
        $this->assertEmpty($result['result'][1]['userMessageReads']);
    }
}
