<?php

declare(strict_types=1);

/**
 * Create Note Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Processing\Note;

use Dvsa\Olcs\Api\Domain\CommandHandler\Processing\Note\Create as CreateCommandHandler;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Create as CreateCommand;
use Dvsa\Olcs\Api\Domain\Repository\Note;
use Dvsa\Olcs\Api\Entity\Note\Note as NoteEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity;

/**
 * Create Note Test
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var CreateCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CreateCommandHandler();
        $this->mockRepo('Note', Note::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            NoteEntity::NOTE_TYPE_APPLICATION,
            NoteEntity::NOTE_TYPE_BUS,
            NoteEntity::NOTE_TYPE_CASE,
            NoteEntity::NOTE_TYPE_LICENCE,
            NoteEntity::NOTE_TYPE_ORGANISATION,
            NoteEntity::NOTE_TYPE_PERMIT,
            NoteEntity::NOTE_TYPE_PERSON,
            NoteEntity::NOTE_TYPE_TRANSPORT_MANAGER
        ];

        $this->references = [
            Entity\Application\Application::class => [
                50 => m::mock(Entity\Application\Application::class)
            ],
            Entity\Bus\BusReg::class => [
                51 => m::mock(Entity\Bus\BusReg::class)
            ],
            Entity\Cases\Cases::class => [
                52 => m::mock(Entity\Cases\Cases::class)
            ],
            Entity\Licence\Licence::class => [
                53 => m::mock(Entity\Licence\Licence::class)
            ],
            Entity\Organisation\Organisation::class => [
                54 => m::mock(Entity\Organisation\Organisation::class)
            ],
            Entity\Tm\TransportManager::class => [
                55 => m::mock(Entity\Tm\TransportManager::class)
            ],
            Entity\Permits\IrhpApplication::class => [
                56 => m::mock(Entity\Permits\IrhpApplication::class)
            ],
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommand')]
    public function testHandleCommand(mixed $data, mixed $expected): void
    {
        $command = CreateCommand::create($data);

        /** @var $note NoteEntity */
        $note = null;

        $this->repoMap['Note']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(NoteEntity::class))
            ->andReturnUsing(
                function (NoteEntity $noteEntity) use (&$note) {
                    $noteEntity->setId(111);
                    $note = $noteEntity;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'note' => 111,
            ],
            'messages' => [
                'Note created'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());

        $this->assertEquals(111, $note->getId());
        $this->assertEquals($expected['comment'], $note->getComment());
        $this->assertEquals($expected['priority'], $note->getPriority());
        $this->assertEquals($expected['type'], $note->getNoteType()->getId());

        if (isset($expected['application'])) {
            $this->assertEquals($expected['application'], $note->getApplication()->getId());
        } else {
            $this->assertNull($note->getApplication());
        }
        if (isset($expected['busReg'])) {
            $this->assertEquals($expected['busReg'], $note->getBusReg()->getId());
        } else {
            $this->assertNull($note->getBusReg());
        }
        if (isset($expected['case'])) {
            $this->assertEquals($expected['case'], $note->getCase()->getId());
        } else {
            $this->assertNull($note->getCase());
        }
        if (isset($expected['licence'])) {
            $this->assertEquals($expected['licence'], $note->getLicence()->getId());
        } else {
            $this->assertNull($note->getLicence());
        }
        if (isset($expected['organisation'])) {
            $this->assertEquals($expected['organisation'], $note->getOrganisation()->getId());
        } else {
            $this->assertNull($note->getOrganisation());
        }
        if (isset($expected['transportManager'])) {
            $this->assertEquals($expected['transportManager'], $note->getTransportManager()->getId());
        } else {
            $this->assertNull($note->getTransportManager());
        }
        if (isset($expected['irhpApplication'])) {
            $this->assertEquals($expected['irhpApplication'], $note->getIrhpApplication()->getId());
        } else {
            $this->assertNull($note->getIrhpApplication());
        }
    }

    public static function dpTestHandleCommand(): array
    {
        return [
            [
                'data' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'application' => 50,
                ],
                'expected' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'type' => NoteEntity::NOTE_TYPE_APPLICATION,
                    'application' => 50,
                ],
            ],
            [
                'data' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'busReg' => 51,
                ],
                'expected' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'type' => NoteEntity::NOTE_TYPE_BUS,
                    'busReg' => 51,
                ],
            ],
            [
                'data' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'case' => 52,
                ],
                'expected' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'type' => NoteEntity::NOTE_TYPE_CASE,
                    'case' => 52,
                ],
            ],
            [
                'data' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'licence' => 53,
                ],
                'expected' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'type' => NoteEntity::NOTE_TYPE_LICENCE,
                    'licence' => 53,
                ],
            ],
            [
                'data' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'organisation' => 54,
                ],
                'expected' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'type' => NoteEntity::NOTE_TYPE_ORGANISATION,
                    'organisation' => 54,
                ],
            ],
            [
                'data' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'transportManager' => 55,
                ],
                'expected' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'type' => NoteEntity::NOTE_TYPE_TRANSPORT_MANAGER,
                    'transportManager' => 55,
                ],
            ],
            [
                'data' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'irhpApplication' => 56,
                ],
                'expected' => [
                    'comment' => 'my comment',
                    'priority' => 'Y',
                    'type' => NoteEntity::NOTE_TYPE_PERMIT,
                    'irhpApplication' => 56,
                ],
            ],
            [
                'data' => [
                    'comment' => 'my comment',
                    'priority' => 'N',
                    'application' => 50,
                    'busReg' => 51,
                    'case' => 52,
                    'licence' => 53,
                    'organisation' => 54,
                    'transportManager' => 55,
                    'irhpApplication' => 56,
                ],
                'expected' => [
                    'comment' => 'my comment',
                    'priority' => 'N',
                    'type' => NoteEntity::NOTE_TYPE_PERMIT,
                    'application' => 50,
                    'busReg' => 51,
                    'case' => 52,
                    'licence' => 53,
                    'organisation' => 54,
                    'transportManager' => 55,
                    'irhpApplication' => 56,
                ],
            ],
        ];
    }
}
