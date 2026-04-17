<?php

declare(strict_types=1);

/**
 * ResetS4Test.php
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Schedule41;

use Dvsa\Olcs\Api\Domain\CommandHandler\Schedule41\ResetS4;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\S4;
use Dvsa\Olcs\Api\Entity\Application\S4 as S4Entity;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\ApproveS4 as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Class ResetS4Test
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Schedule41
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ResetS4Test extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ResetS4();
        $this->mockRepo('S4', S4::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
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
