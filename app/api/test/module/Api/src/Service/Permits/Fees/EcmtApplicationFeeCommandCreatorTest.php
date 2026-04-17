<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Fees;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as CreateFeeCmd;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Api\Service\Permits\Fees\EcmtApplicationFeeCommandCreator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EcmtApplicationFeeCommandCreatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtApplicationFeeCommandCreatorTest extends MockeryTestCase
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

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId);
        $irhpApplication->shouldReceive('getApplicationFeeProductReference')
            ->withNoArgs()
            ->andReturn(FeeTypeEntity::FEE_TYPE_ECMT_APP_PRODUCT_REF);

        $currentDateTime = m::mock(DateTime::class);
        $currentDateTime->shouldReceive('format')
            ->with('Y-m-d')
            ->andReturn($currentDateFormatted);

        $feeType = m::mock(FeeTypeEntity::class);
        $feeType->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($feeTypeId);
        $feeType->shouldReceive('getDescription')
            ->withNoArgs()
            ->andReturn($feeTypeDescription);

        $feeTypeRepo = m::mock(FeeTypeRepository::class);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with(FeeTypeEntity::FEE_TYPE_ECMT_APP_PRODUCT_REF)
            ->andReturn($feeType);

        $createFeeCommand = CreateFeeCmd::create([]);

        $commandCreator = m::mock(CommandCreator::class);
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

        $currentDateTimeFactory = m::mock(CurrentDateTimeFactory::class);
        $currentDateTimeFactory->shouldReceive('create')
            ->withNoArgs()
            ->andReturn($currentDateTime);

        $ecmtApplicationFeeCommandCreator = new EcmtApplicationFeeCommandCreator(
            $feeTypeRepo,
            $commandCreator,
            $currentDateTimeFactory
        );

        $this->assertSame(
            $createFeeCommand,
            $ecmtApplicationFeeCommandCreator->create($irhpApplication, $permitsRequired)
        );
    }
}
