<?php

declare(strict_types=1);

/**
 * DeleteListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\CommandHandler\TmEmployment\DeleteList as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\TmEmployment;
use Dvsa\Olcs\Transfer\Command\TmEmployment\DeleteList as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * DeleteListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteListTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('TmEmployment', TmEmployment::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(['ids' => [4323, 12373]]);

        $ol1 = 'OL1';
        $ol2 = 'OL2';

        $this->repoMap['TmEmployment']->shouldReceive('fetchById')->with(4323)->once()->andReturn($ol1);
        $this->repoMap['TmEmployment']->shouldReceive('delete')->with($ol1)->once()->andReturn();
        $this->repoMap['TmEmployment']->shouldReceive('fetchById')->with(12373)->once()->andReturn($ol2);
        $this->repoMap['TmEmployment']->shouldReceive('delete')->with($ol2)->once()->andReturn();

        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            ['TM Employment ID 4323 deleted', 'TM Employment ID 12373 deleted'],
            $response->getMessages()
        );
    }
}
