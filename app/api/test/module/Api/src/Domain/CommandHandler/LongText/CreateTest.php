<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LongText;

use Dvsa\Olcs\Api\Domain\CommandHandler\LongText\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Api\Entity\System\LongText as LongTextEntity;
use Dvsa\Olcs\Transfer\Command\LongText\Create as CreateCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('LongText', LongTextRepo::class);

        parent::setUp();
    }

    public function testItStoresTheAuthoredContentAgainstItsReferenceKey(): void
    {
        $command = CreateCmd::create([
            'referenceKey' => 'application-declaration-gv79-gb',
            'locale' => 'en_GB',
            'pageName' => 'New application declaration (GB, goods)',
            'description' => 'Shown above the signature',
            'content' => '{"blocks":[{"id":"a","type":"paragraph","data":{"text":"I confirm"}}]}',
        ]);

        $this->repoMap['LongText']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(LongTextEntity::class))
            ->andReturnUsing(function (LongTextEntity $entity): void {
                self::assertSame('application-declaration-gv79-gb', $entity->getReferenceKey());
                self::assertSame('New application declaration (GB, goods)', $entity->getPageName());
                self::assertSame(
                    [['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'I confirm']]],
                    $entity->getContent()['blocks'],
                );
            });

        $result = $this->sut->handleCommand($command);

        self::assertContains('Long Text created', $result->getMessages());
    }

    public function testItRejectsContentThatIsNotAnEditorJsDocument(): void
    {
        $command = CreateCmd::create([
            'referenceKey' => 'application-declaration-gv79-gb',
            'locale' => 'en_GB',
            'pageName' => 'Page',
            'description' => null,
            'content' => '"just a string"',
        ]);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleCommand($command);
    }
}
