<?php

declare(strict_types=1);

/**
 * EndIrhpApplicationsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\EndIrhpApplications as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepository;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Withdraw;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplications as Command;
use Mockery as m;

/**
 * EndIrhpApplicationsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EndIrhpApplicationsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('Licence', LicenceRepository::class);

        $this->sut = new CommandHandler();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $withdrawReason): void
    {
        $licenceId = 52;

        $irhpApplicationNotYetSubmittedId = 22;
        $irhpApplicationNotYetSubmitted = m::mock(IrhpApplication::class);
        $irhpApplicationNotYetSubmitted->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplicationNotYetSubmittedId);
        $irhpApplicationNotYetSubmitted->shouldReceive('getStatus->getId')
            ->withNoArgs()
            ->andReturn(IrhpInterface::STATUS_NOT_YET_SUBMITTED);

        $irhpApplicationUnderConsiderationId = 45;
        $irhpApplicationUnderConsideration = m::mock(IrhpApplication::class);
        $irhpApplicationUnderConsideration->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplicationUnderConsiderationId);
        $irhpApplicationUnderConsideration->shouldReceive('getStatus->getId')
            ->withNoArgs()
            ->andReturn(IrhpInterface::STATUS_UNDER_CONSIDERATION);

        $irhpApplicationAwaitingFeeId = 83;
        $irhpApplicationAwaitingFee = m::mock(IrhpApplication::class);
        $irhpApplicationAwaitingFee->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplicationAwaitingFeeId);
        $irhpApplicationAwaitingFee->shouldReceive('getStatus->getId')
            ->withNoArgs()
            ->andReturn(IrhpInterface::STATUS_AWAITING_FEE);

        $ongoingIrhpApplications = new ArrayCollection(
            [$irhpApplicationNotYetSubmitted, $irhpApplicationUnderConsideration, $irhpApplicationAwaitingFee]
        );

        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getOngoingIrhpApplications')
            ->withNoArgs()
            ->andReturn($ongoingIrhpApplications);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($licence);

        $this->expectedSideEffect(
            CancelApplication::class,
            ['id' => $irhpApplicationNotYetSubmittedId],
            new Result()
        );

        $this->expectedSideEffect(
            Withdraw::class,
            [
                'id' => $irhpApplicationUnderConsiderationId,
                'reason' => $withdrawReason
            ],
            new Result()
        );

        $this->expectedSideEffect(
            Withdraw::class,
            [
                'id' => $irhpApplicationAwaitingFeeId,
                'reason' => $withdrawReason
            ],
            new Result()
        );

        $command = Command::create(
            [
                'id' => $licenceId,
                'reason' => $withdrawReason
            ]
        );
        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Cleared IRHP applications for licence 52'],
            $result->getMessages()
        );
    }

    public static function dpHandleCommand(): array
    {
        return [
            [WithdrawableInterface::WITHDRAWN_REASON_BY_USER],
            [WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED],
        ];
    }
}
