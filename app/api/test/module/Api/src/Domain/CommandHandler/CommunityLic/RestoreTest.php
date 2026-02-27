<?php

declare(strict_types=1);

/**
 * Restore Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Restore;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspension as CommunityLicSuspensionRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspensionReason as CommunityLicSuspensionReasonRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicWithdrawal as CommunityLicWithdrawalRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicWithdrawalReason as CommunityLicWithdrawalReasonRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Restore as Cmd;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCmd;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Restore Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class RestoreTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Restore();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);
        $this->mockRepo('CommunityLicSuspension', CommunityLicSuspensionRepo::class);
        $this->mockRepo('CommunityLicSuspensionReason', CommunityLicSuspensionReasonRepo::class);
        $this->mockRepo('CommunityLicWithdrawal', CommunityLicWithdrawalRepo::class);
        $this->mockRepo('CommunityLicWithdrawalReason', CommunityLicWithdrawalReasonRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLicEntity::STATUS_ACTIVE,
            CommunityLicEntity::STATUS_PENDING
        ];

        parent::initReferences();
    }

    protected function mockDeleteSuspensions(mixed $ids): void
    {
        $mockSuspension = m::mock()
            ->shouldReceive('getId')
            ->andReturn(2)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicSuspension']
            ->shouldReceive('fetchByCommunityLicIds')
            ->with($ids)
            ->andReturn([$mockSuspension])
            ->once()
            ->shouldReceive('delete')
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicSuspensionReason']
            ->shouldReceive('fetchBySuspensionIds')
            ->with([2])
            ->andReturn(['suspensionReason'])
            ->once()
            ->shouldReceive('delete')
            ->with('suspensionReason')
            ->once();
    }

    protected function mockDeleteWithdrawals(mixed $ids): void
    {
        $mockWithdrawal = m::mock()
            ->shouldReceive('getId')
            ->andReturn(2)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicWithdrawal']
            ->shouldReceive('fetchByCommunityLicIds')
            ->with($ids)
            ->andReturn([$mockWithdrawal])
            ->once()
            ->shouldReceive('delete')
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicWithdrawalReason']
            ->shouldReceive('fetchByWithdrawalIds')
            ->with([2])
            ->andReturn(['withdrawalReason'])
            ->once()
            ->shouldReceive('delete')
            ->with('withdrawalReason')
            ->once();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function testHandleCommand(mixed $specifiedDate, mixed $status): void
    {
        $licenceId = 1;
        $communityLicenceIds = [10];

        $data = [
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds
        ];

        $command = Cmd::create($data);

        $mockOfficeCopy = m::mock()
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(CommunityLicEntity::STATUS_ACTIVE)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockCommunityLicence = m::mock(CommunityLicEntity::class)
            ->shouldReceive('getId')
            ->andReturn(10)
            ->once()
            ->shouldReceive('getSpecifiedDate')
            ->andReturn($specifiedDate)
            ->twice()
            ->shouldReceive('changeStatusAndExpiryDate')
            ->with($this->refData[$status], null)
            ->once()
            ->getMock();

        $communityLic = null;

        $mockLicence = m::mock()
            ->shouldReceive('hasCommunityLicenceOfficeCopy')
            ->with($communityLicenceIds)
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchOfficeCopy')
            ->with($licenceId)
            ->andReturn($mockOfficeCopy)
            ->once()
            ->shouldReceive('fetchLicencesByIds')
            ->with($communityLicenceIds)
            ->andReturn([$mockCommunityLicence])
            ->once()
            ->shouldReceive('save')
            ->with(m::type(CommunityLicEntity::class))
            ->andReturnUsing(
                function (CommunityLicEntity $lic) use (&$communityLic) {
                    $communityLic = $lic;
                }
            )
            ->once()
            ->getMock();

        $this->mockDeleteSuspensions($communityLicenceIds);
        $this->mockDeleteWithdrawals($communityLicenceIds);

        $this->expectedSideEffect(
            UpdateTotalCommunityLicencesCmd::class,
            [
                'id' => $licenceId,
            ],
            new Result()
        );

        $expected = [
            'id' => [
                'communityLic10' => 10
            ],
            'messages' => [
                'Community Licence 10 restored'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals($specifiedDate, $communityLic->getSpecifiedDate());
    }

    public static function statusProvider(): array
    {
        return [
            [new DateTime('now'),  CommunityLicEntity::STATUS_ACTIVE],
            [null, CommunityLicEntity::STATUS_PENDING],
        ];
    }

    public function testHandleCommandWithException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $licenceId = 1;
        $communityLicenceIds = [10];

        $data = [
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds
        ];

        $command = Cmd::create($data);

        $mockOfficeCopy = m::mock()
            ->shouldReceive('getStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(CommunityLicEntity::STATUS_WITHDRAWN)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockLicence = m::mock()
            ->shouldReceive('hasCommunityLicenceOfficeCopy')
            ->with($communityLicenceIds)
            ->andReturn(false)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchOfficeCopy')
            ->with($licenceId)
            ->andReturn($mockOfficeCopy)
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
