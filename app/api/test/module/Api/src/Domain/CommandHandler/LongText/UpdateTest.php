<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LongText;

use Dvsa\Olcs\Api\Domain\CommandHandler\LongText\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Api\Entity\System\LongText as LongTextEntity;
use Dvsa\Olcs\Transfer\Command\LongText\Update as UpdateCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('LongText', LongTextRepo::class);

        parent::setUp();
    }

    public function testItUpdatesTheWordingButNotTheReferenceKey(): void
    {
        $entity = LongTextEntity::create('application-declaration-gv79-gb', 'en_GB', 'Old name', 'Old', ['blocks' => []]);

        $this->repoMap['LongText']->shouldReceive('fetchById')->once()->with(7)->andReturn($entity);
        $this->repoMap['LongText']->shouldReceive('save')->once()->with($entity);

        $result = $this->sut->handleCommand(UpdateCmd::create([
            'id' => 7,
            'pageName' => 'New name',
            'description' => 'New description',
            'content' => '{"blocks":[{"id":"a","type":"paragraph","data":{"text":"Updated"}}]}',
        ]));

        self::assertSame('New name', $entity->getPageName());
        self::assertSame('New description', $entity->getDescription());
        self::assertSame('Updated', $entity->getContent()['blocks'][0]['data']['text']);
        self::assertSame(
            'application-declaration-gv79-gb',
            $entity->getReferenceKey(),
            'The reference key is what code addresses this content with and must never change',
        );
        self::assertContains('Long Text updated', $result->getMessages());
    }
}
