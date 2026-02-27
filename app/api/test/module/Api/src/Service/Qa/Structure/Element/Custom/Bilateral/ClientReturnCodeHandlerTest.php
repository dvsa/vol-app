<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\ApplicationCountryRemover;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ClientReturnCodeHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ClientReturnCodeHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ClientReturnCodeHandlerTest extends MockeryTestCase
{
    private $irhpPermitApplication;

    private $qaContext;

    private $applicationCountryRemover;

    private $clientReturnCodeHandler;

    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->irhpPermitApplication);

        $this->applicationCountryRemover = m::mock(ApplicationCountryRemover::class);

        $this->clientReturnCodeHandler = new ClientReturnCodeHandler($this->applicationCountryRemover);
    }

    public function testHandleDestinationCancel(): void
    {
        $country1 = m::mock(Country::class);

        $countries = new ArrayCollection([$country1]);

        $this->irhpPermitApplication->shouldReceive('getIrhpApplication->getCountrys')
            ->withNoArgs()
            ->andReturn($countries);

        $this->assertEquals(
            ClientReturnCodeHandler::FRONTEND_DESTINATION_CANCEL,
            $this->clientReturnCodeHandler->handle($this->qaContext)
        );
    }

    public function testHandleDestinationOverview(): void
    {
        $country1 = m::mock(Country::class);
        $country2 = m::mock(Country::class);
        $country3 = m::mock(Country::class);

        $countries = new ArrayCollection([$country1, $country2, $country3]);

        $this->irhpPermitApplication->shouldReceive('getIrhpApplication->getCountrys')
            ->withNoArgs()
            ->andReturn($countries);

        $this->applicationCountryRemover->shouldReceive('remove')
            ->with($this->irhpPermitApplication)
            ->once();

        $this->assertEquals(
            ClientReturnCodeHandler::FRONTEND_DESTINATION_OVERVIEW,
            $this->clientReturnCodeHandler->handle($this->qaContext)
        );
    }
}
