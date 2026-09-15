<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\MasterTemplate;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\MasterTemplate\Create as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\MasterTemplate as MasterTemplateRepo;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate as MasterTemplateEntity;
use Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate\Create as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create MasterTemplate Test
 */
final class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('MasterTemplate', MasterTemplateRepo::class);

        parent::setUp();
    }

    public function testHandleCommandNormalisesSeedShapedSlotContent(): void
    {
        $seedShaped = [
            'version' => '2.28.2',
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Vehicle Operator Licensing']],
            ],
        ];

        $command = Cmd::create([
            'name' => 'NI Letter Template',
            'templateContent' => '<html>{{FOOTER_CONTENT}}</html>',
            'isDefault' => false,
            'locale' => 'en_NI',
            'footerContent' => $seedShaped,
        ]);

        $captured = null;
        $this->repoMap['MasterTemplate']->shouldReceive('save')
            ->once()
            ->andReturnUsing(function (MasterTemplateEntity $entity) use (&$captured) {
                $captured = $entity->getFooterContent();
                $entity->setId(99);
            });

        $result = $this->sut->handleCommand($command);

        $this->assertSame(99, $result->getId('masterTemplate'));
        $this->assertIsInt($captured['time']);
        $this->assertSame('gen-0', $captured['blocks'][0]['id']);
    }

    public function testHandleCommandRejectsNonEditorJsShapedSlotContent(): void
    {
        $command = Cmd::create([
            'name' => 'Bad Template',
            'templateContent' => '<html></html>',
            'isDefault' => false,
            'locale' => 'en_GB',
            'headerRightContent' => ['blocks' => 'not-an-array'],
        ]);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }
}
