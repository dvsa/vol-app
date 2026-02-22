<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\RestoreListConditionUndertaking as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Variation\RestoreListConditionUndertaking as Command;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Mockery as m;

/**
 * RestoreListConditionUndertakingTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class RestoreListConditionUndertakingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 65,
            'ids' => [73,324]
        ];
        $command = Command::create($data);

        $mockConditionUndertaking1 = m::mock(\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking::class)->makePartial();
        $mockConditionUndertaking1->setId(73);
        $mockConditionUndertaking1->setAction('D');

        $mockConditionUndertaking2 = m::mock(\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking::class)->makePartial();
        $mockConditionUndertaking2->setId(324);

        $mockConditionUndertaking3 = m::mock(\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking::class)->makePartial();
        $mockConditionUndertaking3->setId(27);

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')->with(73)->once()
            ->andReturn($mockConditionUndertaking1);
        $this->repoMap['ConditionUndertaking']->shouldReceive('delete')->with($mockConditionUndertaking1)->once();

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')->with(324)->once()
            ->andReturn($mockConditionUndertaking2);
        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchListForLicConditionVariation')->with(324)->once()
            ->andReturn([$mockConditionUndertaking3]);
        $this->repoMap['ConditionUndertaking']->shouldReceive('delete')->with($mockConditionUndertaking3)->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['ConditionUndertaking ID 73 restored', 'ConditionUndertaking ID 27 restored'],
            $result->getMessages()
        );
    }
}
