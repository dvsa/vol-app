<?php

declare(strict_types=1);

/**
 * Create NoFurtherAction Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmCaseDecision;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision\CreateNoFurtherAction;
use Dvsa\Olcs\Api\Domain\Repository\TmCaseDecision;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as TmCaseDecisionEntity;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\CreateNoFurtherAction as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create NoFurtherAction Test
 */
class CreateNoFurtherActionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateNoFurtherAction();
        $this->mockRepo('TmCaseDecision', TmCaseDecision::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            TmCaseDecisionEntity::DECISION_NO_FURTHER_ACTION,
        ];

        $this->references = [
            CasesEntity::class => [
                11 => m::mock(CasesEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'case' => 11,
            'isMsi' => 'Y',
            'decisionDate' => '2016-01-01',
            'notifiedDate' => '2016-01-01',
            'noFurtherActionReason' => 'testing',
        ];

        $command = Cmd::create($data);

        $this->repoMap['TmCaseDecision']->shouldReceive('save')
            ->once()
            ->with(m::type(TmCaseDecisionEntity::class))
            ->andReturnUsing(
                function (TmCaseDecisionEntity $entity) {
                    $entity->setId(111);
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'tmCaseDecision' => 111,
            ],
            'messages' => [
                'Decision created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
