<?php

declare(strict_types=1);

/**
 * Delete Documents Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\DeleteDocuments;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocuments as Cmd;

/**
 * Delete Documents Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteDocumentsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteDocuments();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['ids' => [123, 321]]);

        $this->expectedSideEffect(DeleteDocument::class, ['id' => 123], new Result());
        $this->expectedSideEffect(DeleteDocument::class, ['id' => 321], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 document(s) deleted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
