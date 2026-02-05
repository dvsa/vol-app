<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Replacement;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Replacement\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\Replacement as ReplacementRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Replacement\Create as CreateCmd;
use Dvsa\Olcs\Api\Entity\System\Replacement as ReplacementEntity;

/**
 * Create Replacement Test
 *
 * @author Andy Newton <andy@vitri.ltd
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('Replacement', ReplacementRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'placeholder' => '{{sometext}}',
            'replacementText' => 'https://maybe/a/url',
        ];
        $command = CreateCmd::create($cmdData);

        $this->repoMap['Replacement']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(ReplacementEntity::class))
            ->andReturnUsing(
                function (ReplacementEntity $replacement) use ($command) {
                    $replacement->setId(1);
                    $this->assertEquals($command->getPlaceholder(), $replacement->getPlaceholder());
                    $this->assertEquals($command->getReplacementText(), $replacement->getReplacementText());
                }
            );

        $this->expectedCacheSideEffect(CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['Replacement' => 1],
            'messages' => [
                "Replacement '1' created",
                'cache update message'
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
