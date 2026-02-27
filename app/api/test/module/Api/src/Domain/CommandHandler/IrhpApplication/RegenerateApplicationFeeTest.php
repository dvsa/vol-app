<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\RegenerateApplicationFee;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;

class RegenerateApplicationFeeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('FeeType', FeeTypeRepo::class);
        $this->sut = new RegenerateApplicationFee();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $irhpApplicationId = 47;
        $licenceId = 32;

        $existingFee1Id = 78;
        $existingFee1 = m::mock(Fee::class);
        $existingFee1->shouldReceive('getId')
            ->andReturn($existingFee1Id);

        $existingFee2Id = 79;
        $existingFee2 = m::mock(Fee::class);
        $existingFee2->shouldReceive('getId')
            ->andReturn($existingFee2Id);

        $existingFees = [$existingFee1, $existingFee2];

        $product1Reference = 'PRODUCT_REF_1';
        $product1Quantity = 4;
        $product1FeeTypeId = 123;
        $product1FeeTypeDescription = 'Product Reference 1 Description';
        $product1FeeTypeFixedValue = 23;

        $product1FeeType = m::mock(FeeType::class);
        $product1FeeType->shouldReceive('getId')
            ->andReturn($product1FeeTypeId);
        $product1FeeType->shouldReceive('getDescription')
            ->andReturn($product1FeeTypeDescription);
        $product1FeeType->shouldReceive('getFixedValue')
            ->andReturn($product1FeeTypeFixedValue);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($product1Reference)
            ->andReturn($product1FeeType);

        $product2Reference = 'PRODUCT_REF_2';
        $product2Quantity = 6;
        $product2FeeTypeId = 456;
        $product2FeeTypeDescription = 'Product Reference 2 Description';
        $product2FeeTypeFixedValue = 35;

        $product2FeeType = m::mock(FeeType::class);
        $product2FeeType->shouldReceive('getId')
            ->andReturn($product2FeeTypeId);
        $product2FeeType->shouldReceive('getDescription')
            ->andReturn($product2FeeTypeDescription);
        $product2FeeType->shouldReceive('getFixedValue')
            ->andReturn($product2FeeTypeFixedValue);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($product2Reference)
            ->andReturn($product2FeeType);

        $productRefsAndQuantities = [
            $product1Reference => $product1Quantity,
            $product2Reference => $product2Quantity,
        ];

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('canCreateOrReplaceApplicationFee')
            ->andReturn(true);
        $irhpApplication->shouldReceive('getOutstandingApplicationFees')
            ->andReturn($existingFees);
        $irhpApplication->shouldReceive('getLicence->getId')
            ->andReturn($licenceId);
        $irhpApplication->shouldReceive('getApplicationFeeProductRefsAndQuantities')
            ->andReturn($productRefsAndQuantities);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);

        $this->expectedSideEffect(
            CancelFee::class,
            ['id' => $existingFee1Id],
            new Result()
        );

        $this->expectedSideEffect(
            CancelFee::class,
            ['id' => $existingFee2Id],
            new Result()
        );

        $this->expectedSideEffect(
            CreateFee::class,
            [
                'licence' => $licenceId,
                'irhpApplication' => $irhpApplicationId,
                'invoicedDate' => date('Y-m-d'),
                'description' => 'Product Reference 1 Description - 4 at £23',
                'feeType' => $product1FeeTypeId,
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'quantity' => $product1Quantity
            ],
            new Result()
        );

        $this->expectedSideEffect(
            CreateFee::class,
            [
                'licence' => $licenceId,
                'irhpApplication' => $irhpApplicationId,
                'invoicedDate' => date('Y-m-d'),
                'description' => 'Product Reference 2 Description - 6 at £35',
                'feeType' => $product2FeeTypeId,
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'quantity' => $product2Quantity
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($irhpApplicationId, $result->getId('irhpApplication'));
        $this->assertEquals(
            ['Refreshed Application fee list'],
            $result->getMessages()
        );
    }

    public function testForbidden(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage(
            'IRHP application is not in the correct state to allow create/replace of Application fee'
        );

        $irhpApplicationId = 47;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('canCreateOrReplaceApplicationFee')
            ->andReturn(false);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($irhpApplicationId)
            ->andReturn($irhpApplication);

        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getId')
            ->andReturn($irhpApplicationId);

        $this->sut->handleCommand($command);
    }
}
