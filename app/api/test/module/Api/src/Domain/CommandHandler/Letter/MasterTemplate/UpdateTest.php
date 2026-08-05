<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\MasterTemplate;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\MasterTemplate\Update as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\MasterTemplate as MasterTemplateRepo;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate as MasterTemplateEntity;
use Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate\Update as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update MasterTemplate Test
 */
final class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('MasterTemplate', MasterTemplateRepo::class);

        parent::setUp();
    }

    public function testHandleCommandNormalisesSeedShapedSlotContent(): void
    {
        // Seed/hand-authored shape: no top-level 'time', blocks without 'id' — the
        // EditorJS parser requires both, so the handler must normalise on the way in.
        $seedShaped = [
            'version' => '2.28.2',
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Office of the Traffic Commissioner']],
            ],
        ];

        $command = Cmd::create([
            'id' => 1,
            'name' => 'Default Letter Template',
            'headerLeftContent' => $seedShaped,
            'version' => 1,
        ]);

        $entity = m::mock(MasterTemplateEntity::class)->makePartial();
        $entity->setId(1);
        $entity->shouldReceive('getName')->andReturn('Default Letter Template');

        $captured = null;
        $entity->shouldReceive('setHeaderLeftContent')
            ->once()
            ->andReturnUsing(function ($content) use (&$captured) {
                $captured = $content;
            });

        $this->repoMap['MasterTemplate']->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($entity);
        $this->repoMap['MasterTemplate']->shouldReceive('save')
            ->with($entity)
            ->once();

        $this->sut->handleCommand($command);

        $this->assertIsInt($captured['time']);
        $this->assertSame('gen-0', $captured['blocks'][0]['id']);
        $this->assertSame($seedShaped['blocks'][0]['data'], $captured['blocks'][0]['data']);
    }

    public function testHandleCommandRejectsNonEditorJsShapedSlotContent(): void
    {
        $command = Cmd::create([
            'id' => 1,
            'name' => 'Default Letter Template',
            'signoffContent' => ['nonsense' => 'no blocks key here'],
            'version' => 1,
        ]);

        $entity = m::mock(MasterTemplateEntity::class)->makePartial();
        $entity->setId(1);

        $this->repoMap['MasterTemplate']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($entity);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }
}
