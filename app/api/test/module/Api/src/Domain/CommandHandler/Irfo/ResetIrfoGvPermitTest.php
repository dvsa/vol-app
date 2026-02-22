<?php

declare(strict_types=1);

/**
 * Reset Irfo Gv Permit Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\ResetIrfoGvPermit;
use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as IrfoGvPermitEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Irfo\ResetIrfoGvPermit as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Reset Irfo Gv Permit Test
 */
class ResetIrfoGvPermitTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ResetIrfoGvPermit();
        $this->mockRepo('IrfoGvPermit', IrfoGvPermit::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoGvPermitEntity::STATUS_PENDING
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 11,
        ];

        /** @var IrfoGvPermitEntity $irfoGvPermit */
        $irfoGvPermit = m::mock(IrfoGvPermitEntity::class)->makePartial();
        $irfoGvPermit->setId(11);
        $irfoGvPermit->shouldReceive('reset')
            ->once()
            ->with(m::type(RefData::class))
            ->andReturn($irfoGvPermit);

        $command = Cmd::create($data);

        $this->repoMap['IrfoGvPermit']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($irfoGvPermit);

        $this->repoMap['IrfoGvPermit']->shouldReceive('save')
            ->once()
            ->with(m::type(IrfoGvPermitEntity::class))
            ->andReturn($irfoGvPermit);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irfoGvPermit' => 11
            ],
            'messages' => [
                'IRFO GV Permit updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
