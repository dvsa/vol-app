<?php

declare(strict_types=1);

/**
 * Reset Irfo Psv Auth Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\ResetIrfoPsvAuth as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuth as IrfoPsvAuthRepo;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as IrfoPsvAuthEntity;
use Dvsa\Olcs\Transfer\Command\Irfo\ResetIrfoPsvAuth as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Reset Irfo Psv Auth Test
 */
class ResetIrfoPsvAuthTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('IrfoPsvAuth', IrfoPsvAuthRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoPsvAuthEntity::STATUS_CNS,
            IrfoPsvAuthEntity::STATUS_RENEW,
            IrfoPsvAuthEntity::STATUS_PENDING,
            IrfoPsvAuthEntity::STATUS_WITHDRAWN,
            IrfoPsvAuthEntity::STATUS_APPROVED,
            IrfoPsvAuthEntity::STATUS_REFUSED,
        ];

        parent::initReferences();
    }

    /**
     * testHandleCommand for all status's
     *
     * @param $status
     * @param $newStatus
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function testHandleCommandOtherStatus(mixed $status, mixed $newStatus): void
    {
        $id = 99;

        $data = [
            'id' => $id,
            'version' => 2,
            'organisation' => 11,
            'irfoPsvAuthType' => 22,
            'validityPeriod' => 1,
            'inForceDate' => '2015-01-01',
            'expiryDate' => '2016-01-01',
            'applicationSentDate' => '2014-01-01',
            'serviceRouteFrom' => 'From',
            'serviceRouteTo' => 'To',
            'journeyFrequency' => IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY,
            'isFeeExemptApplication' => 'Y',
            'isFeeExemptAnnual' => 'Y',
            'exemptionDetails' => 'testing',
            'copiesRequired' => 1,
            'copiesRequiredTotal' => 1,
            'countrys' => ['GB'],
            'irfoPsvAuthNumbers' => [
                ['name' => 'test 1'],
                ['name' => ''],
            ],
        ];

        $command = Cmd::create($data);

        /** @var IrfoPsvAuthEntity $irfoPsvAuth */
        $irfoPsvAuth = m::mock(IrfoPsvAuthEntity::class)->makePartial()->setStatus($this->refData[$status]);
        $irfoPsvAuth->shouldReceive('reset')
            ->once()
            ->with($this->refData[$newStatus])
            ->shouldReceive('getId')
            ->andReturn($id);

        $this->repoMap['IrfoPsvAuth']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($irfoPsvAuth)
            ->shouldReceive('save')
            ->with(m::type(IrfoPsvAuthEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * Data provider, current status and new status expected to be set by resetting
     * @return array
     */
    public static function statusProvider(): array
    {
        return [
            [IrfoPsvAuthEntity::STATUS_REFUSED, IrfoPsvAuthEntity::STATUS_PENDING],
            [IrfoPsvAuthEntity::STATUS_CNS, IrfoPsvAuthEntity::STATUS_RENEW],
            [IrfoPsvAuthEntity::STATUS_WITHDRAWN, IrfoPsvAuthEntity::STATUS_PENDING],
            [IrfoPsvAuthEntity::STATUS_RENEW, IrfoPsvAuthEntity::STATUS_PENDING],
            [IrfoPsvAuthEntity::STATUS_APPROVED, IrfoPsvAuthEntity::STATUS_PENDING],
        ];
    }
}
