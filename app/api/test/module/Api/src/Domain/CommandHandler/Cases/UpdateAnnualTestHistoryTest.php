<?php

declare(strict_types=1);

/**
 * Update AnnualTestHistory Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\UpdateAnnualTestHistory;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateAnnualTestHistory as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Update AnnualTestHistory Test
 */
class UpdateAnnualTestHistoryTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateAnnualTestHistory();
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::Create(
            [
                'id' => 99,
                'annualTestHistory' => ''
            ]
        );

        /** @var CasesEntity $cases */
        $cases = m::mock(CasesEntity::class)->makePartial();
        $cases->shouldReceive('updateAnnualTestHistory')
            ->once();

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($cases)
            ->shouldReceive('save')
            ->with(m::type(CasesEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
    }
}
