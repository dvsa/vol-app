<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\UpdatePenaltiesNote;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\UpdatePenaltiesNote as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * UpdatePenaltiesNoteTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdatePenaltiesNoteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdatePenaltiesNote();
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::Create(
            [
                'id' => 99,
                'penaltiesNote' => 'NOTE'
            ]
        );

        /** @var CasesEntity $cases */
        $cases = m::mock(CasesEntity::class)->makePartial();

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($cases);
        $this->repoMap['Cases']->shouldReceive('save')->with(m::type(CasesEntity::class))->once();

        $this->sut->handleCommand($command);

        $this->assertSame('NOTE', $cases->getPenaltiesNote());
    }
}
