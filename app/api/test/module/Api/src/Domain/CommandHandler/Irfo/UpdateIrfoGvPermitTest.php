<?php

declare(strict_types=1);

/**
 * Update Irfo Gv Permit Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\UpdateIrfoGvPermit;
use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as IrfoGvPermitEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermitType;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoGvPermit as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update Irfo Gv Permit Test
 */
class UpdateIrfoGvPermitTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateIrfoGvPermit();
        $this->mockRepo('IrfoGvPermit', IrfoGvPermit::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            IrfoGvPermitType::class => [
                22 => m::mock(IrfoGvPermitType::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 11,
            'version' => 1,
            'irfoGvPermitType' => 22,
            'yearRequired' => 2014,
            'inForceDate' => '2015-01-01',
            'isFeeExempt' => 'Y',
            'noOfCopies' => 1
        ];

        /** @var IrfoGvPermitEntity $irfoGvPermit */
        $irfoGvPermit = m::mock(IrfoGvPermitEntity::class)->makePartial();
        $irfoGvPermit->setId(11);

        $command = Cmd::create($data);

        $this->repoMap['IrfoGvPermit']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($irfoGvPermit);

        /** @var IrfoGvPermitEntity $savedIrfoGvPermit */
        $savedIrfoGvPermit = null;

        $this->repoMap['IrfoGvPermit']->shouldReceive('save')
            ->once()
            ->with(m::type(IrfoGvPermitEntity::class))
            ->andReturnUsing(
                function (IrfoGvPermitEntity $irfoGvPermit) use (&$savedIrfoGvPermit) {
                    $irfoGvPermit->setId(111);
                    $savedIrfoGvPermit = $irfoGvPermit;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irfoGvPermit' => 111
            ],
            'messages' => [
                'IRFO GV Permit updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->references[IrfoGvPermitType::class][22], $savedIrfoGvPermit->getIrfoGvPermitType());
        $this->assertEquals(2014, $savedIrfoGvPermit->getYearRequired());
        $this->assertEquals('2015-01-01', $savedIrfoGvPermit->getInForceDate()->format('Y-m-d'));
        $this->assertEquals('Y', $savedIrfoGvPermit->getIsFeeExempt());
        $this->assertEquals(1, $savedIrfoGvPermit->getNoOfCopies());
    }
}
