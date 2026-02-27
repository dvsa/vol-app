<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Common;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\ApplicationFeesClearer;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsUpdater;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsUpdaterTest extends MockeryTestCase
{
    public function testUpdate(): void
    {
        $licenceId = 47;
        $irhpApplicationId = 33;
        $irhpPermitApplicationId = 407;

        $currentDateTimeString = '2020-04-25';
        $currentDateTime = m::mock(DateTime::class);
        $currentDateTime->shouldReceive('format')
            ->with('Y-m-d')
            ->andReturn($currentDateTimeString);

        $productReference1 = 'PRODUCT_REFERENCE_TYPE_1';
        $productReference1Quantity = 14;
        $productReference1FeeTypeId = 100;
        $productReference1FeeType = m::mock(FeeType::class);
        $productReference1FeeType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($productReference1FeeTypeId);

        $expectedProductReference1FeeParams = [
            'licence' => $licenceId,
            'irhpApplication' => $irhpApplicationId,
            'irhpPermitApplication' => $irhpPermitApplicationId,
            'invoicedDate' => $currentDateTimeString,
            'feeStatus' => Fee::STATUS_OUTSTANDING,
            'feeType' => $productReference1FeeTypeId,
            'quantity' => $productReference1Quantity,
        ];

        $productReference2 = 'PRODUCT_REFERENCE_TYPE_2';
        $productReference2Quantity = 8;
        $productReference2FeeTypeId = 200;
        $productReference2FeeType = m::mock(FeeType::class);
        $productReference2FeeType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($productReference2FeeTypeId);

        $expectedProductReference2FeeParams = [
            'licence' => $licenceId,
            'irhpApplication' => $irhpApplicationId,
            'irhpPermitApplication' => $irhpPermitApplicationId,
            'invoicedDate' => $currentDateTimeString,
            'feeStatus' => Fee::STATUS_OUTSTANDING,
            'feeType' => $productReference2FeeTypeId,
            'quantity' => $productReference2Quantity,
        ];

        $productRefsAndQuantities = [
            $productReference1 => $productReference1Quantity,
            $productReference2 => $productReference2Quantity,
        ];

        $updatedAnswers = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 14,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 8,
        ];

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId);

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpPermitApplicationId);
        $irhpPermitApplication->shouldReceive('getIrhpApplication')
            ->withNoArgs()
            ->andReturn($irhpApplication);
        $irhpPermitApplication->shouldReceive('getBilateralFeeProductRefsAndQuantities')
            ->withNoArgs()
            ->andReturn($productRefsAndQuantities);
        $irhpPermitApplication->shouldReceive('updateBilateralRequired')
            ->with($updatedAnswers)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);
        $irhpPermitApplicationRepo->shouldReceive('save')
            ->with($irhpPermitApplication)
            ->once()
            ->globally()
            ->ordered();

        $feeTypeRepo = m::mock(FeeTypeRepository::class);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with($productReference1)
            ->andReturn($productReference1FeeType);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with($productReference2)
            ->andReturn($productReference2FeeType);

        $createFeeCommand1 = CreateFee::create([]);
        $createFeeCommand2 = CreateFee::create([]);

        $commandCreator = m::mock(CommandCreator::class);
        $commandCreator->shouldReceive('create')
            ->with(CreateFee::class, $expectedProductReference1FeeParams)
            ->andReturn($createFeeCommand1);
        $commandCreator->shouldReceive('create')
            ->with(CreateFee::class, $expectedProductReference2FeeParams)
            ->andReturn($createFeeCommand2);

        $commandHandlerManager = m::mock(CommandHandlerManager::class);
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($createFeeCommand1, false)
            ->once();
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($createFeeCommand2, false)
            ->once();

        $applicationFeesClearer = m::mock(ApplicationFeesClearer::class);
        $applicationFeesClearer->shouldReceive('clear')
            ->with($irhpPermitApplication)
            ->once();

        $currentDateTimeFactory = m::mock(CurrentDateTimeFactory::class);
        $currentDateTimeFactory->shouldReceive('create')
            ->withNoArgs()
            ->andReturn($currentDateTime);

        $noOfPermitsUpdater = new NoOfPermitsUpdater(
            $irhpPermitApplicationRepo,
            $feeTypeRepo,
            $commandCreator,
            $commandHandlerManager,
            $applicationFeesClearer,
            $currentDateTimeFactory
        );

        $noOfPermitsUpdater->update($irhpPermitApplication, $updatedAnswers);
    }
}
