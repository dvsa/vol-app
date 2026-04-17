<?php

declare(strict_types=1);

/**
 * Update Case Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\UpdateCase;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateCase as Cmd;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;

/**
 * Update Case Test
 */
class UpdateCaseTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateCase();
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CasesEntity::LICENCE_CASE_TYPE,
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::Create(
            [
                'id' => 99,
                'categorys' => ['case_cat_compl_proh'],
                'outcomes' => ['case_o_opr'],
                'caseType' => CasesEntity::LICENCE_CASE_TYPE
            ]
        );

        /** @var CasesEntity $cases */
        $cases = m::mock(CasesEntity::class)->makePartial();
        $cases->shouldReceive('update')
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
