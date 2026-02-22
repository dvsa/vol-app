<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\Permits;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as IrhpPermitWindowRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CancelUnsubmittedBilateral as CancelUnsubmittedBilateralCmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\Permits\CancelUnsubmittedBilateral as CancelUnsubmittedBilateralHandler;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication as CancelApplicationCmd;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries as UpdateCountriesCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Test the cancel unsubmitted bilateral command
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CancelUnsubmittedBilateralTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CancelUnsubmittedBilateralHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitWindow', IrhpPermitWindowRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $openNorwayWindowId = 6;
        $openFranceWindowId = 8;
        $closedNorwayWindowId = 4;
        $closedFranceWindowId = 10;

        $irhpApplication1NorwayIpa = m::mock(IrhpPermitApplication::class);
        $irhpApplication1NorwayIpa->shouldReceive('getIrhpPermitWindow->getId')
            ->withNoArgs()
            ->andReturn($closedNorwayWindowId);
        $irhpApplication1Id = 455;
        $irhpApplication1 = m::mock(IrhpApplication::class);
        $irhpApplication1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplication1Id);
        $irhpApplication1->shouldReceive('getCountryIds')
            ->withNoArgs()
            ->andReturn([Country::ID_BELARUS, Country::ID_NORWAY]);
        $irhpApplication1->shouldReceive('getIrhpPermitApplicationByCountryId')
            ->with(Country::ID_NORWAY)
            ->andReturn($irhpApplication1NorwayIpa);

        $irhpApplication2FranceIpa = m::mock(IrhpPermitApplication::class);
        $irhpApplication2FranceIpa->shouldReceive('getIrhpPermitWindow->getId')
            ->withNoArgs()
            ->andReturn($closedFranceWindowId);
        $irhpApplication2Id = 458;
        $irhpApplication2 = m::mock(IrhpApplication::class);
        $irhpApplication2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplication2Id);
        $irhpApplication2->shouldReceive('getCountryIds')
            ->withNoArgs()
            ->andReturn([Country::ID_NORWAY, Country::ID_FRANCE]);
        $irhpApplication2->shouldReceive('getIrhpPermitApplicationByCountryId')
            ->with(Country::ID_NORWAY)
            ->andReturnNull();
        $irhpApplication2->shouldReceive('getIrhpPermitApplicationByCountryId')
            ->with(Country::ID_FRANCE)
            ->andReturn($irhpApplication2FranceIpa);

        $irhpApplication3FranceIpa = m::mock(IrhpPermitApplication::class);
        $irhpApplication3FranceIpa->shouldReceive('getIrhpPermitWindow->getId')
            ->withNoArgs()
            ->andReturn($openFranceWindowId);
        $irhpApplication3Id = 467;
        $irhpApplication3 = m::mock(IrhpApplication::class);
        $irhpApplication3->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplication3Id);
        $irhpApplication3->shouldReceive('getCountryIds')
            ->withNoArgs()
            ->andReturn([Country::ID_NORWAY, Country::ID_FRANCE]);
        $irhpApplication3->shouldReceive('getIrhpPermitApplicationByCountryId')
            ->with(Country::ID_NORWAY)
            ->andReturnNull();
        $irhpApplication3->shouldReceive('getIrhpPermitApplicationByCountryId')
            ->with(Country::ID_FRANCE)
            ->andReturn($irhpApplication3FranceIpa);

        $irhpApplications = [
            $irhpApplication1,
            $irhpApplication2,
            $irhpApplication3,
        ];

        $this->repoMap['IrhpApplication']->shouldReceive('fetchNotYetSubmittedBilateralApplications')
            ->withNoArgs()
            ->andReturn($irhpApplications);

        $openNorwayWindow = m::mock(IrhpPermitWindow::class);
        $openNorwayWindow->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($openNorwayWindowId);
        $openNorwayWindow->shouldReceive('getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn(Country::ID_NORWAY);

        $openFranceWindow = m::mock(IrhpPermitWindow::class);
        $openFranceWindow->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($openFranceWindowId);
        $openFranceWindow->shouldReceive('getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn(Country::ID_FRANCE);

        $openWindows = [$openNorwayWindow, $openFranceWindow];

        $this->repoMap['IrhpPermitWindow']->shouldReceive('fetchOpenWindowsByType')
            ->with(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, m::type(DateTime::class), true)
            ->andReturn($openWindows);

        $this->expectedSideEffect(
            CancelApplicationCmd::class,
            [
                'id' => $irhpApplication1Id,
            ],
            new Result()
        );

        $this->expectedSideEffect(
            UpdateCountriesCmd::class,
            [
                'id' => $irhpApplication2Id,
                'countries' => [Country::ID_NORWAY],
            ],
            new Result()
        );

        $cmd = CancelUnsubmittedBilateralCmd::create([]);
        $this->sut->handleCommand($cmd);
    }
}
