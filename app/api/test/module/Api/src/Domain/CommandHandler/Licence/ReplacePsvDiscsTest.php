<?php

declare(strict_types=1);

/**
 * Replace Psv Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\ReplacePsvDiscs;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs;
use Dvsa\Olcs\Transfer\Command\Licence\ReplacePsvDiscs as Cmd;
use Dvsa\Olcs\Transfer\Command\Licence\VoidPsvDiscs;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Replace Psv Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReplacePsvDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ReplacePsvDiscs();

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [

        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [
                111, 222
            ],
            'licence' => 123
        ];

        $command = Cmd::create($data);

        $expectedData = $data;
        $result1 = new Result();
        $result1->addMessage('Voided');
        $this->expectedSideEffect(VoidPsvDiscs::class, $expectedData, $result1);

        $expectedData = [
            'licence' => 123,
            'amount' => 2,
            'isCopy' => 'Y'
        ];
        $result2 = new Result();
        $result2->addMessage('Created');
        $this->expectedSideEffect(CreatePsvDiscs::class, $expectedData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Voided',
                'Created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
