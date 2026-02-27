<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\ExistingIrhpPermitApplicationHandler;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\QuestionHandlerDelegator;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\ApplicationAnswersClearer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ExistingIrhpPermitApplicationHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ExistingIrhpPermitApplicationHandlerTest extends MockeryTestCase
{
    public const EXISTING_STOCK_ID = 10;

    public const REQUIRED_PERMITS = [
        'requiredPermitsKey1' => 'requiredPermitsValue1',
        'requiredPermitsKey2' => 'requiredPermitsValue2'
    ];

    private $applicationStep1;

    private $applicationStep2;

    private $irhpPermitApplication;

    private $irhpPermitApplicationRepo;

    private $irhpPermitStockRepo;

    private $applicationAnswersClearer;

    private $questionHandlerDelegator;

    private $existingIrhpPermitApplicationHandler;

    public function setUp(): void
    {
        $this->applicationStep1 = m::mock(ApplicationStep::class);

        $this->applicationStep2 = m::mock(ApplicationStep::class);

        $applicationSteps = [$this->applicationStep1, $this->applicationStep2];

        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $this->irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getId')
            ->withNoArgs()
            ->andReturn(self::EXISTING_STOCK_ID);
        $this->irhpPermitApplication->shouldReceive('getActiveApplicationPath->getApplicationSteps')
            ->withNoArgs()
            ->andReturn($applicationSteps);

        $this->irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);

        $this->irhpPermitStockRepo = m::mock(IrhpPermitStockRepository::class);

        $this->applicationAnswersClearer = m::mock(ApplicationAnswersClearer::class);

        $this->questionHandlerDelegator = m::mock(QuestionHandlerDelegator::class);

        $this->existingIrhpPermitApplicationHandler = new ExistingIrhpPermitApplicationHandler(
            $this->irhpPermitApplicationRepo,
            $this->irhpPermitStockRepo,
            $this->applicationAnswersClearer,
            $this->questionHandlerDelegator
        );
    }

    public function testHandleStockChanged(): void
    {
        $newStockId = 11;

        $irhpPermitWindow = m::mock(IrhpPermitWindow::class);

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getOpenWindow')
            ->withNoArgs()
            ->andReturn($irhpPermitWindow);

        $this->irhpPermitStockRepo->shouldReceive('fetchById')
            ->with($newStockId)
            ->andReturn($irhpPermitStock);

        $this->applicationAnswersClearer->shouldReceive('clear')
            ->with($this->irhpPermitApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitApplication->shouldReceive('updateIrhpPermitWindow')
            ->with($irhpPermitWindow)
            ->once()
            ->globally()
            ->ordered();

        $this->questionHandlerDelegator->shouldReceive('delegate')
            ->with($this->irhpPermitApplication, $this->applicationStep1, self::REQUIRED_PERMITS)
            ->once()
            ->globally()
            ->ordered();

        $this->questionHandlerDelegator->shouldReceive('delegate')
            ->with($this->irhpPermitApplication, $this->applicationStep2, self::REQUIRED_PERMITS)
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitApplication->shouldReceive('updateCheckAnswers')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitApplicationRepo->shouldReceive('save')
            ->with($this->irhpPermitApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->existingIrhpPermitApplicationHandler->handle(
            $this->irhpPermitApplication,
            $newStockId,
            self::REQUIRED_PERMITS
        );
    }

    public function testHandleStockNotChanged(): void
    {
        $this->questionHandlerDelegator->shouldReceive('delegate')
            ->with($this->irhpPermitApplication, $this->applicationStep1, self::REQUIRED_PERMITS)
            ->once()
            ->globally()
            ->ordered();

        $this->questionHandlerDelegator->shouldReceive('delegate')
            ->with($this->irhpPermitApplication, $this->applicationStep2, self::REQUIRED_PERMITS)
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitApplication->shouldReceive('updateCheckAnswers')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitApplicationRepo->shouldReceive('save')
            ->with($this->irhpPermitApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->existingIrhpPermitApplicationHandler->handle(
            $this->irhpPermitApplication,
            self::EXISTING_STOCK_ID,
            self::REQUIRED_PERMITS
        );
    }
}
