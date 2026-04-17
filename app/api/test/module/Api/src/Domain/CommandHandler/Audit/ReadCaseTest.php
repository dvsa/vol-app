<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Domain\CommandHandler\Audit\ReadCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Cases\CasesReadAudit;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Read Case Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReadCaseTest extends AbstractCommandHandlerTestCase
{
    public const USER_ID = 9999;

    /** @var m\MockInterface|User */
    private $mockUser;
    /** @var \Dvsa\Olcs\Transfer\Command\Audit\ReadCase */
    private $command;

    public function setUp(): void
    {
        $this->sut = new ReadCase();
        $this->mockRepo('CasesReadAudit', Repository\CasesReadAudit::class);
        $this->mockRepo('Cases', Repository\Bus::class);

        $this->mockUser = m::mock(User::class)->makePartial();
        $this->mockUser->setId(self::USER_ID);

        $mockAuthSrv = m::mock(AuthorizationService::class);
        $mockAuthSrv->shouldReceive('getIdentity->getUser')->andReturn($this->mockUser);
        $this->mockedSmServices[AuthorizationService::class] = $mockAuthSrv;

        //  command
        $data = [
            'id' => '111',
        ];
        $this->command = \Dvsa\Olcs\Transfer\Command\Audit\ReadCase::create($data);

        parent::setUp();
    }

    public function testHandleCommandWhenExists(): void
    {
        $this->repoMap['CasesReadAudit']->shouldReceive('fetchOneOrMore')
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
        $entity = m::mock(Cases::class);

        $this->repoMap['CasesReadAudit']->shouldReceive('fetchOneOrMore')->once()
            ->with(self::USER_ID, 111, \DateTime::class)
            ->andReturn(null)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(CasesReadAudit::class))
            ->andReturnUsing(
                function (CasesReadAudit $record) use ($entity) {
                    static::assertSame($this->mockUser, $record->getUser());
                    static::assertSame($entity, $record->getCase());
                }
            );

        $this->repoMap['Cases']->shouldReceive('fetchById')
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
