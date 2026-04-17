<?php

declare(strict_types=1);

/**
 * ApproveS4Test.php
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Schedule41;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\S4;
use Dvsa\Olcs\Api\Entity\Application\S4 as S4Entity;
use Dvsa\Olcs\Api\Domain\CommandHandler\Schedule41\ApproveS4;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\ApproveS4 as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create SubmissionAction Test
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ApproveS4Test extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ApproveS4();
        $this->mockRepo('S4', S4::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            's4_sts_approved'
        ];

        $this->references = [
            S4Entity::class => [
                1 => m::mock(S4Entity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
            'isTrueS4' => true,
        ];

        $command = Cmd::create($data);

        $this->repoMap['S4']
            ->shouldReceive('save')
            ->once()
            ->with(
                $this->references[S4Entity::class][1]
            );

        $this->sut->handleCommand($command);
    }
}
