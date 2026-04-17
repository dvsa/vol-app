<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Domain\CommandHandler\Audit\ReadApplication;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationReadAudit;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Read Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReadApplicationTest extends AbstractCommandHandlerTestCase
{
    public const USER_ID = 9999;

    /** @var m\MockInterface|User */
    private $mockUser;
    /** @var  \Dvsa\Olcs\Transfer\Command\Audit\ReadApplication  */
    private $command;

    public function setUp(): void
    {
        $this->sut = new ReadApplication();
        $this->mockRepo('ApplicationReadAudit', Repository\ApplicationReadAudit::class);
        $this->mockRepo('Application', Repository\Application::class);

        $this->mockUser = m::mock(User::class)->makePartial();
        $this->mockUser->setId(self::USER_ID);

        $mockAuthSrv = m::mock(AuthorizationService::class);
        $mockAuthSrv->shouldReceive('getIdentity->getUser')->andReturn($this->mockUser);
        $this->mockedSmServices[AuthorizationService::class] = $mockAuthSrv;

        //  command
        $data = [
            'id' => '111'
        ];
        $this->command = \Dvsa\Olcs\Transfer\Command\Audit\ReadApplication::create($data);

        parent::setUp();
    }

    public function testHandleCommandWhenExists(): void
    {
        $this->repoMap['ApplicationReadAudit']->shouldReceive('fetchOneOrMore')
            ->once()
            ->with(self::USER_ID, 111, \DateTime::class)
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
        $entity = m::mock(Application::class);

        $this->repoMap['ApplicationReadAudit']->shouldReceive('fetchOneOrMore')->once()
            ->with(self::USER_ID, 111, \DateTime::class)
            ->andReturn(null)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(ApplicationReadAudit::class))
            ->andReturnUsing(
                function (ApplicationReadAudit $record) use ($entity) {
                    static::assertSame($this->mockUser, $record->getUser());
                    static::assertSame($entity, $record->getApplication());
                }
            );

        $this->repoMap['Application']->shouldReceive('fetchById')
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
