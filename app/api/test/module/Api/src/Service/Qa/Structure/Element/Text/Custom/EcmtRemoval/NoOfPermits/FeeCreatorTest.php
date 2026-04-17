<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as CreateFeeCmd;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\FeeCreator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * FeeCreatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FeeCreatorTest extends MockeryTestCase
{
    public function testCreate(): void
    {
        $licenceId = 7;
        $irhpApplicationId = 53;

        $permitsRequired = 47;

        $currentDateFormatted = '2019-06-03';

        $feeTypeDescription = 'ECMT Permit';
        $feeDescription = 'ECMT Permit - 47 permits';
        $feeTypeId = 1003;

        $outstandingIssueFee1Id = 76;
        $outstandingIssueFee2Id = 78;

        $outstandingIssueFee1 = m::mock(FeeEntity::class);
        $outstandingIssueFee1->shouldReceive('getId')
            ->andReturn($outstandingIssueFee1Id);

        $outstandingIssueFee2 = m::mock(FeeEntity::class);
        $outstandingIssueFee2->shouldReceive('getId')
            ->andReturn($outstandingIssueFee2Id);

        $outstandingIssueFees = [
            $outstandingIssueFee1,
            $outstandingIssueFee2
        ];

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getLicence->getId')
            ->andReturn($licenceId);
        $irhpApplication->shouldReceive('getOutstandingIrfoPermitFees')
            ->andReturn($outstandingIssueFees);

        $currentDateTime = m::mock(DateTime::class);
        $currentDateTime->shouldReceive('format')
            ->with('Y-m-d')
            ->andReturn($currentDateFormatted);

        $feeType = m::mock(FeeTypeEntity::class);
        $feeType->shouldReceive('getId')
            ->andReturn($feeTypeId);
        $feeType->shouldReceive('getDescription')
            ->andReturn($feeTypeDescription);

        $feeTypeRepo = m::mock(FeeTypeRepository::class);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with(FeeTypeEntity::FEE_TYPE_ECMT_REMOVAL_ISSUE_PRODUCT_REF)
            ->andReturn($feeType);

        $cancelFeeCommand1 = CancelFeeCmd::create([]);

        $cancelFeeCommand2 = CancelFeeCmd::create([]);

        $createFeeCommand = CreateFeeCmd::create([]);

        $commandCreator = m::mock(CommandCreator::class);
        $commandCreator->shouldReceive('create')
            ->with(CancelFeeCmd::class, ['id' => $outstandingIssueFee1Id])
            ->andReturn($cancelFeeCommand1);
        $commandCreator->shouldReceive('create')
            ->with(CancelFeeCmd::class, ['id' => $outstandingIssueFee2Id])
            ->andReturn($cancelFeeCommand2);
        $commandCreator->shouldReceive('create')
            ->with(
                CreateFeeCmd::class,
                [
                    'licence' => $licenceId,
                    'irhpApplication' => $irhpApplicationId,
                    'invoicedDate' => $currentDateFormatted,
                    'description' => $feeDescription,
                    'feeType' => $feeTypeId,
                    'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                    'quantity' => $permitsRequired
                ]
            )
            ->andReturn($createFeeCommand);

        $commandHandlerManager = m::mock(CommandHandlerManager::class);
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($cancelFeeCommand1, false)
            ->once();
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($cancelFeeCommand2, false)
            ->once();
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($createFeeCommand, false)
            ->once();

        $currentDateTimeFactory = m::mock(CurrentDateTimeFactory::class);
        $currentDateTimeFactory->shouldReceive('create')
            ->andReturn($currentDateTime);

        $feeCreator = new FeeCreator(
            $feeTypeRepo,
            $commandCreator,
            $commandHandlerManager,
            $currentDateTimeFactory
        );

        $feeCreator->create($irhpApplication, $permitsRequired);
    }
}
