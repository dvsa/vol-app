<?php

declare(strict_types=1);

/**
 * Move document test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Transfer\Command\Document\MoveDocument as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\MoveDocument as CommandHandler;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Transfer\Command\Document\CopyDocument as CopyDocumentCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Move document test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class MoveDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Document', DocumentRepo::class);

        parent::setUp();
    }

    public function testHandleCommandWithAppplication(): void
    {
        $data = [
            'targetId' => 1,
            'type'     => 'app',
            'ids'      => [2]
        ];
        $command = Cmd::create($data);

        $copyResult = new Result();
        $copyResult->addId('document111', 111);
        $this->expectedSideEffect(CopyDocumentCmd::class, $data, $copyResult);

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with(2)
            ->andReturn('document')
            ->shouldReceive('delete')
            ->with('document')
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);
        $res = $result->toArray();
        $this->assertEquals(111, $res['id']['document111']);
        $this->assertEquals('Document(s) moved', $res['messages'][0]);
    }
}
