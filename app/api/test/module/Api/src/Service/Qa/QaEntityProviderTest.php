<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\QaEntityProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * QaEntityProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class QaEntityProviderTest extends MockeryTestCase
{
    private $irhpApplicationId = 17;

    private $irhpPermitApplicationId = 50;

    private $irhpApplication;

    private $irhpPermitApplication;

    private $irhpApplicationRepo;

    private $irhpPermitApplicationRepo;

    private $qaEntityProvider;

    public function setUp(): void
    {
        $this->irhpApplication = m::mock(IrhpApplication::class);

        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->irhpApplicationRepo = m::mock(IrhpApplicationRepository::class);
        $this->irhpApplicationRepo->shouldReceive('fetchById')
            ->with($this->irhpApplicationId)
            ->andReturn($this->irhpApplication);

        $this->irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);
        $this->irhpPermitApplicationRepo->shouldReceive('fetchById')
            ->with($this->irhpPermitApplicationId)
            ->andReturn($this->irhpPermitApplication);

        $this->qaEntityProvider = new QaEntityProvider($this->irhpApplicationRepo, $this->irhpPermitApplicationRepo);
    }

    public function testGetWithIrhpApplication(): void
    {
        $this->assertSame(
            $this->irhpApplication,
            $this->qaEntityProvider->get($this->irhpApplicationId, null)
        );
    }

    public function testGetWithIrhpPermitApplication(): void
    {
        $this->irhpPermitApplication->shouldReceive('getIrhpApplication')
            ->withNoArgs()
            ->andReturn($this->irhpApplication);

        $this->assertSame(
            $this->irhpPermitApplication,
            $this->qaEntityProvider->get($this->irhpApplicationId, $this->irhpPermitApplicationId)
        );
    }

    public function testGetNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Mismatched IrhpApplication and IrhpPermitApplication');

        $this->irhpPermitApplication->shouldReceive('getIrhpApplication')
            ->withNoArgs()
            ->andReturn(m::mock(IrhpApplication::class));

        $this->qaEntityProvider->get($this->irhpApplicationId, $this->irhpPermitApplicationId);
    }
}
