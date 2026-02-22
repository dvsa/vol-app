<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitSector;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitSector\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota as PermitSectorQuotaRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpPermitSector\Update as UpdateCmd;

/**
 * Update IRHP Permit Sector Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('IrhpPermitSectorQuota', PermitSectorQuotaRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $idQuota1 = 111;
        $idQuota2 = 222;
        $idQuota3 = 333;

        $amountQuota1 = 444;
        $amountQuota2 = 555;
        $amountQuota3 = 666;

        $cmdData = [
            'sectors' => [
                $idQuota1 => $amountQuota1,
                $idQuota2 => $amountQuota2,
                $idQuota3 => $amountQuota3,
            ]
        ];

        $command = UpdateCmd::create($cmdData);

        $quota1 = m::mock(IrhpPermitSectorQuota::class);
        $quota1->shouldReceive('getId')->once()->withNoArgs()->andReturn($idQuota1);
        $quota1->shouldReceive('update')->once()->with($amountQuota1);

        $quota2 = m::mock(IrhpPermitSectorQuota::class);
        $quota2->shouldReceive('getId')->once()->withNoArgs()->andReturn($idQuota2);
        $quota2->shouldReceive('update')->once()->with($amountQuota2);

        $quota3 = m::mock(IrhpPermitSectorQuota::class);
        $quota3->shouldReceive('getId')->once()->withNoArgs()->andReturn($idQuota3);
        $quota3->shouldReceive('update')->once()->with($amountQuota3);

        $returnedRecords = [$quota1, $quota2, $quota3];

        $this->repoMap['IrhpPermitSectorQuota']
            ->shouldReceive('fetchByIds')
            ->once()
            ->with([$idQuota1, $idQuota2, $idQuota3])
            ->andReturn($returnedRecords);

        $this->repoMap['IrhpPermitSectorQuota']
            ->shouldReceive('save')
            ->times(3)
            ->with(m::type(IrhpPermitSectorQuota::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'IrhpPermitSectorQuota' => [
                    0 => $idQuota1,
                    1 => $idQuota2,
                    2 => $idQuota3,
                ],
            ],
            'messages' => ['Irhp Permit Sector Quota updated'],
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
