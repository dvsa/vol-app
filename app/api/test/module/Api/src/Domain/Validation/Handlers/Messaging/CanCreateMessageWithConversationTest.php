<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\Repository\MessagingConversation as ConversationRepo;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanCreateMessageWithConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

class CanCreateMessageWithConversationTest extends AbstractHandlerTestCase
{
    /**
     * @var CanCreateMessageWithConversation
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanCreateMessageWithConversation();

        parent::setUp();
    }

    /**
     * @dataProvider dpTestIsValid
     */
    public function testIsValid($canAccess, $hasPermission, $isOpen, $expected)
    {
        /** @var CommandInterface $dto */
        $conversationId = 1;
        $permission = Permission::CAN_REPLY_TO_CONVERSATIONS;
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted($permission, $hasPermission);

        if ($hasPermission === false) {
            $this->assertSame($expected, $this->sut->isValid($dto));
        } else {
            $mockRepo = $this->mockRepo(ConversationRepo::class);

            $mockConversationEntity = m::mock(MessagingConversation::class);
            $mockConversationEntity->shouldReceive('getIsClosed')->andReturn(!$isOpen);

            $mockRepo->shouldReceive('fetchById')->with($conversationId)->andReturn($mockConversationEntity);

            $this->setIsValid('canAccessConversation', [$conversationId], $canAccess);

            $dto->shouldReceive('getConversation')->once()->andReturn($conversationId);

            $this->assertSame($expected, $this->sut->isValid($dto));
        }
    }

    public function dpTestIsValid()
    {
        return [
            [true, true, true, true],
            [true, true, false, false],
            [true, false, true, false],
            [true, false, false, false],
            [false, true, true, false],
            [false, true, false, false],
            [false, false, true, false],
            [false, false, false, false],
        ];
    }
}
