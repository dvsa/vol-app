<?php

declare(strict_types=1);

/**
 * Reopen Case Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ReopenCase;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\ReopenCase as ReopenCmd;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Reopen Case Test
 */
class ReopenCaseTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ReopenCase();
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $case = 24;

        $command = ReopenCmd::Create(['id' => $case]);

        $mockCase = m::mock(CasesEntity::class);
        $mockCase->shouldReceive('reopen')->once()->andReturnSelf();
        $mockCase->shouldReceive('getId')->once()->andReturn($case);

        $this->repoMap['Cases']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockCase)
            ->shouldReceive('save')
            ->with(m::type(CasesEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
