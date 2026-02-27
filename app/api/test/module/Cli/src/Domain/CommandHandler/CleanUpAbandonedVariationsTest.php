<?php

declare(strict_types=1);

/**
 * Clean up abandoned variations Test
 */

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Cli\Domain\CommandHandler\CleanUpAbandonedVariations as CommandHandler;
use Dvsa\Olcs\Cli\Domain\Command\CleanUpAbandonedVariations as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Variation\DeleteVariation;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;

/**
 * Clean up abandoned variations
 */
class CleanUpAbandonedVariationsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', Application::class);

        $this->mockedSmServices['config'] = [
            'batch_config' => [
                'clean-abandoned-variations' => [
                    'older-than' => '4 hours'
                ]
            ]
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {

        $mockVariation1 = m::mock(ApplicationEntity::class);
        $mockVariation1->shouldReceive('getId')->andReturn(1);

        $mockVariation2 = m::mock(ApplicationEntity::class);
        $mockVariation2->shouldReceive('getId')->andReturn(2);

        $mockVariation3 = m::mock(ApplicationEntity::class);
        $mockVariation3->shouldReceive('getId')->andReturn(3);

        $this->repoMap['Application']->shouldReceive('fetchAbandonedVariations')
            ->once()->andReturn([$mockVariation1, $mockVariation2, $mockVariation3]);

        $this->commandHandler->shouldReceive('handleCommand')
            ->times(3)
            ->with(m::type(DeleteVariation::class), false);

        $response = $this->sut->handleCommand(Command::create([]));

        $deleteCommand = m::mock(DeleteVariation::class);
        $deleteCommand->shouldReceive('handleCommand');

        $expected = [
            'id' => [
                'variation 1' => 1,
                'variation 2' => 2,
                'variation 3' => 3
            ],
            'messages' => [
                '3 abandoned variation records deleted'
            ]
        ];

        $this->assertEquals($expected, $response->toArray());
    }
}
