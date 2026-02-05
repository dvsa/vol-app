<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\PostSubmit;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Service\Qa\PostSubmit\IrhpApplicationPostSubmitHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpApplicationPostSubmitHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpApplicationPostSubmitHandlerTest extends MockeryTestCase
{
    private $irhpApplication;

    private $irhpPermitRepo;

    private $irhpApplicationPostSubmitHandler;

    public function setUp(): void
    {
        $this->irhpApplication = m::mock(IrhpApplication::class);

        $this->irhpPermitRepo = m::mock(IrhpPermitRepository::class);

        $this->irhpApplicationPostSubmitHandler = new IrhpApplicationPostSubmitHandler($this->irhpPermitRepo);
    }

    public function testHandleWhenEcmtRemoval(): void
    {
        $irhpPermit1 = m::mock(IrhpPermit::class);
        $irhpPermit1->shouldReceive('regenerateIssueDateAndExpiryDate')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitRepo->shouldReceive('save')
            ->with($irhpPermit1)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermit2 = m::mock(IrhpPermit::class);
        $irhpPermit2->shouldReceive('regenerateIssueDateAndExpiryDate')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitRepo->shouldReceive('save')
            ->with($irhpPermit2)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermits = [$irhpPermit1, $irhpPermit2];

        $this->irhpApplication->shouldReceive('getIrhpPermitType->isEcmtRemoval')
            ->withNoArgs()
            ->andReturnTrue();
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication->getIrhpPermits')
            ->withNoArgs()
            ->andReturn($irhpPermits);

        $this->irhpApplicationPostSubmitHandler->handle($this->irhpApplication);
    }

    public function testHandleWhenNotEcmtRemoval(): void
    {
        $this->irhpApplication->shouldReceive('getIrhpPermitType->isEcmtRemoval')
            ->withNoArgs()
            ->andReturnFalse();
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->never();

        $this->irhpApplicationPostSubmitHandler->handle($this->irhpApplication);
    }
}
