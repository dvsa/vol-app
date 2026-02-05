<?php

declare(strict_types=1);

/**
 * Update TxcInbox Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\UpdateTxcInbox;
use Dvsa\Olcs\Api\Domain\Repository\TxcInbox as TxcInboxRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\UpdateTxcInbox as Cmd;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox as TxcInboxEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Update TxcInbox Test
 */
class UpdateTxcInboxTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateTxcInbox();
        $this->mockRepo('TxcInbox', TxcInboxRepo::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class)->makePartial();

        parent::setUp();
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommand(): void
    {
        $id = 99;
        $localAuthorityId = 888;

        $user = m::mock();
        $mockLocalAuthority = m::mock(\Dvsa\Olcs\Api\Entity\Bus\LocalAuthority::class)->makePartial();

        $mockLocalAuthority->shouldReceive('getId')
            ->andReturn($localAuthorityId);

        $user->shouldReceive('getLocalAuthority')
            ->andReturn($mockLocalAuthority);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $command = Cmd::Create(
            [
                'ids' => [$id]
            ]
        );

        /** @var TxcInboxEntity $txcInbox */
        $txcInbox = m::mock(TxcInboxEntity::class);
        $txcInbox->shouldReceive('setFileRead')
            ->with('Y')
            ->once()
            ->shouldReceive('getId')
            ->andReturn($id);

        $this->repoMap['TxcInbox']->shouldReceive('fetchByIdsForLocalAuthority')
            ->with($command->getIds(), $localAuthorityId, Query::HYDRATE_OBJECT)
            ->andReturn([$txcInbox])
            ->shouldReceive('save')
            ->with(m::type(TxcInboxEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommandNotLocalAuthority(): void
    {
        $id = 99;

        $user = m::mock();

        $user->shouldReceive('getLocalAuthority')
            ->andReturnNull();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $command = Cmd::Create(
            [
                'ids' => [$id]
            ]
        );

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $this->sut->handleCommand($command);
    }
}
