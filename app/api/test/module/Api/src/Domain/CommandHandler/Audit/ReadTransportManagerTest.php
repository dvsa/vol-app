<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Domain\CommandHandler\Audit\ReadTransportManager;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerReadAudit;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Read Transport Manager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReadTransportManagerTest extends AbstractCommandHandlerTestCase
{
    public const USER_ID = 9999;

    /** @var m\MockInterface|User */
    private $mockUser;
    /** @var  \Dvsa\Olcs\Transfer\Command\Audit\ReadTransportManager */
    private $command;

    public function setUp(): void
    {
        $this->sut = new ReadTransportManager();
        $this->mockRepo('TransportManagerReadAudit', Repository\TransportManagerReadAudit::class);
        $this->mockRepo('TransportManager', Repository\Bus::class);

        $this->mockUser = m::mock(User::class)->makePartial();
        $this->mockUser->setId(self::USER_ID);

        $mockAuthSrv = m::mock(AuthorizationService::class);
        $mockAuthSrv->shouldReceive('getIdentity->getUser')->andReturn($this->mockUser);
        $this->mockedSmServices[AuthorizationService::class] = $mockAuthSrv;

        //  command
        $data = [
            'id' => '111',
        ];
        $this->command = \Dvsa\Olcs\Transfer\Command\Audit\ReadTransportManager::create($data);

        parent::setUp();
    }

    public function testHandleCommandWhenExists(): void
    {
        $this->repoMap['TransportManagerReadAudit']->shouldReceive('fetchOneOrMore')
            ->once()
            ->with(self::USER_ID, 111, \DateTime::class)
            ->andReturn(['foo']);

        $result = $this->sut->handleCommand($this->command);

        $expected = [
            'id' => [],
            'messages' => [
                'Audit record exists',
            ],
        ];

        static::assertEquals($expected, $result->toArray());
    }

    public function testHandleCommand(): void
    {
        $entity = m::mock(TransportManager::class);

        $this->repoMap['TransportManagerReadAudit']->shouldReceive('fetchOneOrMore')->once()
            ->with(self::USER_ID, 111, \DateTime::class)
            ->andReturn(null)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(TransportManagerReadAudit::class))
            ->andReturnUsing(
                function (TransportManagerReadAudit $record) use ($entity) {
                    static::assertSame($this->mockUser, $record->getUser());
                    static::assertSame($entity, $record->getTransportManager());
                }
            );

        $this->repoMap['TransportManager']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($entity);

        $result = $this->sut->handleCommand($this->command);

        $expected = [
            'id' => [],
            'messages' => [
                'Audit record created',
            ],
        ];

        static::assertEquals($expected, $result->toArray());
    }
}
