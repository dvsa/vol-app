<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitJurisdiction;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitJurisdictionQuota;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitJurisdiction\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitJurisdictionQuota as PermitJurisdictionQuotaRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpPermitJurisdiction\Update as UpdateCmd;

/**
 * Update IRHP Permit Jurisdiction Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('IrhpPermitJurisdictionQuota', PermitJurisdictionQuotaRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $idQuotaG = 111;
        $idQuotaM = 222;
        $idQuotaN = 333;

        $amountQuotaG = 444;
        $amountQuotaM = 555;
        $amountQuotaN = 666;

        $cmdData = [
            'trafficAreas' => [
                $idQuotaG => $amountQuotaG,
                $idQuotaM => $amountQuotaM,
                $idQuotaN => $amountQuotaN,
            ],
        ];

        $command = UpdateCmd::create($cmdData);

        $quotaG = m::mock(IrhpPermitJurisdictionQuota::class);
        $quotaG->shouldReceive('getId')->once()->withNoArgs()->andReturn($idQuotaG);
        $quotaG->shouldReceive('update')->once()->with($amountQuotaG);

        $quotaM = m::mock(IrhpPermitJurisdictionQuota::class);
        $quotaM->shouldReceive('getId')->once()->withNoArgs()->andReturn($idQuotaM);
        $quotaM->shouldReceive('update')->once()->with($amountQuotaM);

        $quotaN = m::mock(IrhpPermitJurisdictionQuota::class);
        $quotaN->shouldReceive('getId')->once()->withNoArgs()->andReturn($idQuotaN);
        $quotaN->shouldReceive('update')->once()->with($amountQuotaN);

        $returnedRecords = [$quotaG, $quotaM, $quotaN];

        $this->repoMap['IrhpPermitJurisdictionQuota']
            ->shouldReceive('fetchByIds')
            ->once()
            ->with([$idQuotaG, $idQuotaM, $idQuotaN])
            ->andReturn($returnedRecords);

        $this->repoMap['IrhpPermitJurisdictionQuota']
            ->shouldReceive('save')
            ->times(3)
            ->with(m::type(IrhpPermitJurisdictionQuota::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'IrhpPermitJurisdictionQuota' => [
                    0 => $idQuotaG,
                    1 => $idQuotaM,
                    2 => $idQuotaN,
                ],
            ],
            'messages' => ['Irhp Permit Jurisdiction Quota updated'],
        ];

        self::assertEquals($expected, $result->toArray());
    }
}
