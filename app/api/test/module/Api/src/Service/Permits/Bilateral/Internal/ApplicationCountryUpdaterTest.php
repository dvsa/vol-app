<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\ApplicationCountryUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\ExistingIrhpPermitApplicationHandler;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal\IrhpPermitApplicationCreator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationCountryUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationCountryUpdaterTest extends MockeryTestCase
{
    public const COUNTRY_ID = 'DE';

    public const STOCK_ID = 44;

    public const REQUIRED_PERMITS = [
        'key1' => 'value1',
        'key2' => 'value2',
    ];

    private $irhpApplication;

    private $irhpPermitApplication;

    private $irhpPermitApplicationCreator;

    private $existingIrhpPermitApplicationHandler;

    private $applicationCountryUpdater;

    public function setUp(): void
    {
        $this->irhpApplication = m::mock(IrhpApplication::class);

        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->irhpPermitApplicationCreator = m::mock(IrhpPermitApplicationCreator::class);

        $this->existingIrhpPermitApplicationHandler = m::mock(ExistingIrhpPermitApplicationHandler::class);
        $this->existingIrhpPermitApplicationHandler->shouldReceive('handle')
            ->with($this->irhpPermitApplication, self::STOCK_ID, self::REQUIRED_PERMITS)
            ->once();

        $this->applicationCountryUpdater = new ApplicationCountryUpdater(
            $this->irhpPermitApplicationCreator,
            $this->existingIrhpPermitApplicationHandler
        );
    }

    public function testUpdateIrhpPermitApplicationExists(): void
    {
        $this->irhpApplication->shouldReceive('getIrhpPermitApplicationByStockCountryId')
            ->with(self::COUNTRY_ID)
            ->andReturn($this->irhpPermitApplication);

        $this->applicationCountryUpdater->update(
            $this->irhpApplication,
            self::COUNTRY_ID,
            self::STOCK_ID,
            self::REQUIRED_PERMITS
        );
    }

    public function testUpdateIrhpPermitApplicationNotFound(): void
    {
        $this->irhpApplication->shouldReceive('getIrhpPermitApplicationByStockCountryId')
            ->with(self::COUNTRY_ID)
            ->andReturnNull();

        $this->irhpPermitApplicationCreator->shouldReceive('create')
            ->with($this->irhpApplication, self::STOCK_ID)
            ->once()
            ->andReturn($this->irhpPermitApplication);

        $this->applicationCountryUpdater->update(
            $this->irhpApplication,
            self::COUNTRY_ID,
            self::STOCK_ID,
            self::REQUIRED_PERMITS
        );
    }
}
