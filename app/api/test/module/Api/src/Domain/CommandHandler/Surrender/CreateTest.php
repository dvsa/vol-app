<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Surrender;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Surrender\Create as Sut;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Surrender as SurrenderRepo;
use Dvsa\Olcs\Api\Entity\Surrender as SurrenderEntity;
use Dvsa\Olcs\Transfer\Command\Surrender\Create as Cmd;
use Mockery as m;

class CreateTest extends AbstractCommandHandlerTestCase
{
    public const LIC_ID = 111;

    /** @var Sut */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('Surrender', SurrenderRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];
        parent::setUp();
    }

    public function testHandleCommandWithNoSurrender(): void
    {
        $command = $this->createCommand();

        $licence = $this->getTestingLicence();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->once()
            ->andReturn($licence);

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->once()
            ->andThrow(NotFoundException::class);

        $this->repoMap['Surrender']
            ->shouldReceive('save')
            ->with(m::type(SurrenderEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Surrender successfully created.'], $result->getMessages());

        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleCommandWithWithdrawnSurrender(): void
    {
        $command = $this->createCommand();

        $surrenderEntity = m::mock(SurrenderEntity::class);
        $surrenderEntity->shouldReceive('getStatus->getId')->andReturn('surr_sts_withdrawn');
        $surrenderEntity->shouldReceive('getID')->andReturn('1');
        $surrenderEntity->shouldReceive('setStatus')->once();

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->once()
            ->andReturn($surrenderEntity);

        $this->repoMap['Surrender']
            ->shouldReceive('save')
            ->with($surrenderEntity)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Surrender successfully restarted after being withdrawn.'], $result->getMessages());

        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleCommandWithIncorrectSurrenderStatus(): void
    {
        $command = $this->createCommand();

        $surrenderEntity = m::mock(SurrenderEntity::class);
        $surrenderEntity->shouldReceive('getStatus->getId')->andReturn('surr_sts_start');

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->once()
            ->andReturn($surrenderEntity);

        $this->expectException(ForbiddenException::class);
        $this->sut->handleCommand($command);
    }

    protected function createCommand(): mixed
    {
        $data = [
            'id' => self::LIC_ID,
        ];

        return Cmd::create($data);
    }
}
