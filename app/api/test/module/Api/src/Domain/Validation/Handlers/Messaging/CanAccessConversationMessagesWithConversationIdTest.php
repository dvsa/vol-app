<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanAccessConversationMessagesWithConversationId;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

class CanAccessConversationMessagesWithConversationIdTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessConversationMessagesWithConversationId
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessConversationMessagesWithConversationId();

        parent::setUp();
    }

    /**
     * @dataProvider dpTestIsValid
     */
    public function testIsValid($canAccess, $hasPermission, $expected)
    {
        /** @var CommandInterface $dto */
        $conversationId = 1;
        $dto = m::mock(CommandInterface::class);
        if ($hasPermission) {
            $dto->shouldReceive('getConversation')->once()->andReturn($conversationId);
        }

        $permission = Permission::CAN_LIST_MESSAGES;

        $this->setIsGranted($permission, $hasPermission);

        $this->setIsValid('canAccessConversation', [$conversationId], $canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function dpTestIsValid()
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, true, false],
        ];
    }
}
