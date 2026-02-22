<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Common;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsConditionalUpdater;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsUpdater;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsConditionalUpdaterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsConditionalUpdaterTest extends MockeryTestCase
{
    public const EXISTING_BILATERAL_REQUIRED = [
        IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 5,
        IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 7,
        IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null,
    ];

    private $irhpPermitApplication;

    private $noOfPermitsUpdater;

    private $noOfPermitsConditionalUpdater;

    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $this->irhpPermitApplication->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn(self::EXISTING_BILATERAL_REQUIRED);

        $this->noOfPermitsUpdater = m::mock(NoOfPermitsUpdater::class);

        $this->noOfPermitsConditionalUpdater = new NoOfPermitsConditionalUpdater($this->noOfPermitsUpdater);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpUpdateValueChanged')]
    public function testUpdateValueChanged(mixed $updatedAnswers): void
    {
        $this->noOfPermitsUpdater->shouldReceive('update')
            ->with($this->irhpPermitApplication, $updatedAnswers)
            ->once();

        $this->noOfPermitsConditionalUpdater->update($this->irhpPermitApplication, $updatedAnswers);
    }

    public static function dpUpdateValueChanged(): array
    {
        return [
            [
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 10,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 7,
                    IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null,
                ]
            ],
            [
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 5,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 11,
                    IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null,
                ]
            ],
            [
                [
                    IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 3,
                    IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 4,
                    IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null,
                ]
            ],
        ];
    }

    public function testUpdateValueNotChanged(): void
    {
        $this->noOfPermitsUpdater->shouldReceive('update')
            ->never();

        $this->noOfPermitsConditionalUpdater->update($this->irhpPermitApplication, self::EXISTING_BILATERAL_REQUIRED);
    }
}
