<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepository;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\ApplicationFeesClearer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationFeesClearerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationFeesClearerTest extends MockeryTestCase
{
    public function testRemove(): void
    {
        $fee1Id = 27;
        $fee2Id = 46;
        $fee3Id = 61;

        $commandCreator = m::mock(CommandCreator::class);

        $commandHandlerManager = m::mock(CommandHandlerManager::class);

        $feeRepo = m::mock(FeeRepository::class);

        $fee1 = m::mock(Fee::class);
        $fee1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($fee1Id);
        $fee1->shouldReceive('removeIrhpPermitApplicationAssociation')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $feeRepo->shouldReceive('save')
            ->with($fee1)
            ->once()
            ->globally()
            ->ordered();

        $fee2 = m::mock(Fee::class);
        $fee2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($fee2Id);
        $fee2->shouldReceive('removeIrhpPermitApplicationAssociation')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $feeRepo->shouldReceive('save')
            ->with($fee2)
            ->once()
            ->globally()
            ->ordered();

        $fee3 = m::mock(Fee::class);
        $fee3->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($fee3Id);
        $fee3->shouldReceive('removeIrhpPermitApplicationAssociation')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $feeRepo->shouldReceive('save')
            ->with($fee3)
            ->once()
            ->globally()
            ->ordered();

        $cancelFeeCommand1 = CancelFeeCmd::create([]);

        $cancelFeeCommand2 = CancelFeeCmd::create([]);

        $commandCreator->shouldReceive('create')
            ->with(CancelFeeCmd::class, ['id' => $fee1Id])
            ->andReturn($cancelFeeCommand1);
        $commandCreator->shouldReceive('create')
            ->with(CancelFeeCmd::class, ['id' => $fee2Id])
            ->andReturn($cancelFeeCommand2);

        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($cancelFeeCommand1, false)
            ->once();
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($cancelFeeCommand2, false)
            ->once();

        $outstandingFees = [$fee1, $fee2];
        $fees = [$fee1, $fee2, $fee3];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getOutstandingFees')
            ->withNoArgs()
            ->andReturn($outstandingFees);
        $irhpPermitApplication->shouldReceive('getFees')
            ->withNoArgs()
            ->andReturn($fees);

        $applicationFeesClearer = new ApplicationFeesClearer($commandCreator, $commandHandlerManager, $feeRepo);

        $applicationFeesClearer->clear($irhpPermitApplication);
    }
}
