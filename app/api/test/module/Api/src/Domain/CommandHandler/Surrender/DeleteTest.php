<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Surrender;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Surrender\Delete as Sut;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Surrender as SurrenderRepo;
use Dvsa\Olcs\Api\Entity\Surrender as SurrenderEntity;
use Dvsa\Olcs\Transfer\Command\Surrender\Delete as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class DeleteTest extends AbstractCommandHandlerTestCase
{
    /** @var Sut */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('Surrender', SurrenderRepo::class);

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1
        ];

        $command = Cmd::create($data);

        $surrenderEntity = m::mock(SurrenderEntity::class);

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->with($data['id'])
            ->andReturn($surrenderEntity)
            ->once();

        $this->repoMap['Surrender']
            ->shouldReceive('delete')
            ->with(m::type(SurrenderEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame($data['id'], $result->getId('id' . $data['id']));
        $this->assertSame(['surrender for licence Id ' . $data['id'] . ' deleted'], $result->getMessages());

        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleCommandException(): void
    {
        $data = [
            'id' => 1
        ];
        $command = Cmd::create($data);

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->with($data['id'])
            ->andThrow(NotFoundException::class)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['surrender for licence Id ' . $data['id'] . ' not found'], $result->getMessages());

        $this->assertInstanceOf(Result::class, $result);
    }
}
