<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\DeleteSi as Sut;
use Dvsa\Olcs\Api\Domain\Repository\SeriousInfringement as SiRepo;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Si\DeleteSi as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * DeleteSi Test
 */
class DeleteSiTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('SeriousInfringement', SiRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 123;

        $command = Cmd::Create(
            [
                'id' => $id,
            ]
        );

        $si = m::mock(SiEntity::class)->makePartial();
        $si->setId($id);
        $si->shouldReceive('getCase->isErru')->once()->andReturn(false);

        $this->repoMap['SeriousInfringement']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($si)
            ->shouldReceive('delete')
            ->with(m::type(SiEntity::class))
            ->once();

        $expected = [
            'id' => [
                'id' => $id,
            ],
            'messages' => [
                'Serious Infringement deleted'
            ]
        ];

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandThrowsErruException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $id = 123;

        $si = m::mock(SiEntity::class);
        $si->shouldReceive('getCase->isErru')->once()->andReturn(true);

        $this->repoMap['SeriousInfringement']
            ->shouldReceive('fetchUsingId')
            ->andReturn($si);

        $data = [
            'id' => $id,
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }
}
