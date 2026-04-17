<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Cqrs\CommandCreator;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\ApplicationCountryRemover;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationCountryRemoverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationCountryRemoverTest extends MockeryTestCase
{
    public function testRemove(): void
    {
        $irhpApplicationId = 57;
        $countryCodeForDeletion = 'DE';

        $country1 = m::mock(Country::class);
        $country1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn('FR');
        $country2 = m::mock(Country::class);
        $country2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn('DE');
        $country3 = m::mock(Country::class);
        $country3->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn('ES');

        $irhpApplication = m::mock(IrhpPermitApplication::class);
        $irhpApplication->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($irhpApplicationId);
        $irhpApplication->shouldReceive('getCountrys')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$country1, $country2, $country3]));

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpApplication')
            ->withNoArgs()
            ->andReturn($irhpApplication);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getCountry->getId')
            ->withNoArgs()
            ->andReturn($countryCodeForDeletion);

        $updateCountriesCommand = m::mock(UpdateCountries::class);

        $expectedCommandParams = [
            'id' => $irhpApplicationId,
            'countries' => ['FR', 'ES']
        ];

        $commandCreator = m::mock(CommandCreator::class);
        $commandCreator->shouldReceive('create')
            ->with(UpdateCountries::class, $expectedCommandParams)
            ->andReturn($updateCountriesCommand);

        $commandHandlerManager = m::mock(CommandHandlerManager::class);
        $commandHandlerManager->shouldReceive('handleCommand')
            ->with($updateCountriesCommand, false)
            ->once();

        $applicationCountryRemover = new ApplicationCountryRemover($commandCreator, $commandHandlerManager);

        $applicationCountryRemover->remove($irhpPermitApplication);
    }
}
