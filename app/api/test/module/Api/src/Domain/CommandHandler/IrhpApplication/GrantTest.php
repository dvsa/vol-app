<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtApggAppGranted;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermSuccessful;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\Grant;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Service\Permits\GrantabilityChecker;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;

class GrantTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('FeeType', FeeTypeRepo::class);
        $this->sut = new Grant();

        $this->mockedSmServices = [
            'PermitsGrantabilityChecker' => m::mock(GrantabilityChecker::class),
            'EventHistoryCreator' => m::mock(EventHistoryCreator::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpInterface::STATUS_AWAITING_FEE
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $isEcmtShortTerm, mixed $issueFeeProductReference, mixed $expectedEmailCmd): void
    {
        $irhpApplicationId = 55;

        $irhpApplication = m::mock(IrhpApplication::class);
        $feeType = m::mock(FeeType::class);

        $this->mockedSmServices['PermitsGrantabilityChecker']->shouldReceive('isGrantable')
            ->with($irhpApplication)
            ->andReturn(true);

        $irhpApplication->shouldReceive('canBeGranted')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $irhpApplication->shouldReceive('grant')
            ->once()
            ->with($this->refData[IrhpInterface::STATUS_AWAITING_FEE])
            ->globally()
            ->ordered();

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $irhpApplication->shouldReceive('getFirstIrhpPermitApplication->countPermitsAwarded')
            ->once()
            ->withNoArgs()
            ->andReturn(10);

        $irhpApplication->shouldReceive('getIssueFeeProductReference')
            ->once()
            ->withNoArgs()
            ->andReturn($issueFeeProductReference);

        $irhpApplication->shouldReceive('getLicence->getId')
            ->once()
            ->withNoArgs()
            ->andReturn(7);

        $irhpApplication->shouldReceive('getId')
            ->times(3)
            ->withNoArgs()
            ->andReturn(55);

        $irhpApplication->shouldReceive('getIrhpPermitType->isEcmtShortTerm')
            ->once()
            ->withNoArgs()
            ->andReturn($isEcmtShortTerm);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($issueFeeProductReference)
            ->andReturn($feeType);

        $feeType->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn('IRHP GV ECMT permit monthly');

        $feeType->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(40084);

        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($irhpApplication, EventHistoryTypeEntity::IRHP_APPLICATION_GRANTED)
            ->once();

        $this->expectedSideEffect(
            CreateFee::class,
            [
                'licence' => 7,
                'irhpApplication' => $irhpApplicationId,
                'invoicedDate' => date("Y-m-d"),
                'description' => 'IRHP GV ECMT permit monthly',
                'feeType' => 40084,
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'quantity' => 10,
            ],
            new Result()
        );

        $this->expectedEmailQueueSideEffect(
            $expectedEmailCmd,
            ['id' => $irhpApplicationId],
            $irhpApplicationId,
            new Result()
        );

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['IRHP application granted'],
            $result->getMessages()
        );

        $this->assertEquals($irhpApplicationId, $result->getId('irhpApplication'));
    }

    public static function dpHandleCommand(): array
    {
        return [
            [
                'isEcmtShortTerm' => true,
                'issueFeeProductReference' => FeeType::FEE_TYPE_ECMT_SHORT_TERM_ISSUE_PRODUCT_REF,
                'expectedEmailCmd' => SendEcmtShortTermSuccessful::class,
            ],
            [
                'isEcmtShortTerm' => false,
                'issueFeeProductReference' => FeeType::FEE_TYPE_IRHP_ISSUE,
                'expectedEmailCmd' => SendEcmtApggAppGranted::class,
            ],
        ];
    }

    public function testHandleCommandCantGrantFromEntity(): void
    {
        $irhpApplicationId = 66;
        $irhpApplication = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->mockedSmServices['PermitsGrantabilityChecker']->shouldReceive('isGrantable')
            ->with($irhpApplication)
            ->andReturn(true);

        $irhpApplication->shouldReceive('canBeGranted')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $command = m::mock(CommandInterface::class);

        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Grant::ERR_IRHP_GRANT_CANNOT_GRANT);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandCantGrantFromService(): void
    {
        $irhpApplicationId = 66;
        $irhpApplication = m::mock(IrhpApplication::class);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $this->mockedSmServices['PermitsGrantabilityChecker']->shouldReceive('isGrantable')
            ->with($irhpApplication)
            ->andReturn(false);

        $command = m::mock(CommandInterface::class);

        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(Grant::ERR_IRHP_GRANT_TOO_MANY_PERMITS);

        $this->sut->handleCommand($command);
    }
}
