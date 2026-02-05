<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Audit;

use DateTime;
use Dvsa\Olcs\Api\Domain\CommandHandler\Audit\ReadIrhpApplication;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplicationReadAudit;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Audit\ReadIrhpApplication as ReadIrhpApplicationCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Read Irhp Application Test
 */
class ReadIrhpApplicationTest extends AbstractCommandHandlerTestCase
{
    public const USER_ID = 9999;

    /** @var m\MockInterface|User */
    private $mockUser;

    /** @var  ReadIrhpApplicationCommand */
    private $command;

    public function setUp(): void
    {
        $this->sut = new ReadIrhpApplication();
        $this->mockRepo('IrhpApplicationReadAudit', Repository\IrhpApplicationReadAudit::class);
        $this->mockRepo('IrhpApplication', Repository\IrhpApplication::class);

        $this->mockUser = m::mock(User::class)->makePartial();
        $this->mockUser->setId(self::USER_ID);

        $mockAuthSrv = m::mock(AuthorizationService::class);
        $mockAuthSrv->shouldReceive('getIdentity->getUser')->andReturn($this->mockUser);
        $this->mockedSmServices[AuthorizationService::class] = $mockAuthSrv;

        //  command
        $data = [
            'id' => 111
        ];
        $this->command = ReadIrhpApplicationCommand::create($data);

        parent::setUp();
    }

    public function testHandleCommandWhenExists(): void
    {
        $this->repoMap['IrhpApplicationReadAudit']->shouldReceive('fetchOneOrMore')
            ->once()
            ->with(self::USER_ID, 111, DateTime::class)
            ->andReturn(['foo']);

        $result = $this->sut->handleCommand($this->command);

        $expected = [
            'id' => [],
            'messages' => [
                'Audit record exists'
            ]
        ];

        static::assertEquals($expected, $result->toArray());
    }

    public function testHandleCommand(): void
    {
        $entity = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplicationReadAudit']->shouldReceive('fetchOneOrMore')->once()
            ->with(self::USER_ID, 111, DateTime::class)
            ->andReturn(null)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(IrhpApplicationReadAudit::class))
            ->andReturnUsing(
                function (IrhpApplicationReadAudit $record) use ($entity) {
                    static::assertSame($this->mockUser, $record->getUser());
                    static::assertSame($entity, $record->getIrhpApplication());
                }
            );

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($entity);

        $result = $this->sut->handleCommand($this->command);

        $expected = [
            'id' => [],
            'messages' => [
                'Audit record created'
            ]
        ];

        static::assertEquals($expected, $result->toArray());
    }
}
