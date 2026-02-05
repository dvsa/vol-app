<?php

declare(strict_types=1);

/**
 * Update Note Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Processing\Note;

use Dvsa\Olcs\Api\Domain\CommandHandler\Processing\Note\Update as UpdateCommandHandler;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Update as UpdateCommand;
use Dvsa\Olcs\Api\Domain\Repository\Note;
use Dvsa\Olcs\Api\Entity\Note\Note as NoteEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update Note Test
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var UpdateCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new UpdateCommandHandler();
        $this->mockRepo('Note', Note::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 150;
        $version = 2;

        $data = [
            'id' => $id,
            'version' => $version,
            'priority' => 'Y'
        ];

        $command = UpdateCommand::create($data);

        $this->repoMap['Note']
            ->shouldReceive('fetchById')
            ->with($id, \Doctrine\Orm\Query::HYDRATE_OBJECT, $version)
            ->andReturn(
                m::mock(NoteEntity::class)
                    ->shouldReceive('setPriority')
                    ->once()
                    ->with('Y')
                    ->shouldReceive('getId')
                    ->andReturn($id)
                    ->getMock()
            )
            ->shouldReceive('save')
            ->once();

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'note' => $id,
            ],
            'messages' => [
                'Note updated'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }
}
