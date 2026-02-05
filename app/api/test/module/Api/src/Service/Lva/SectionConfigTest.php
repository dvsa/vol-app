<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Lva;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Service\Lva\SectionConfig;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Section Config Test
 * @package CommonTest\Service\Data
 */
class SectionConfigTest extends MockeryTestCase
{
    public function testGetAll(): void
    {
        $sut = new SectionConfig();

        $all = $sut->getAll();

        $totalSections = count($all);

        // undertakings sections should have all sections bar itself as a prerequisite
        $this->assertEquals(
            ($totalSections - 1),
            count($all['undertakings']['prerequisite'][0])
        );
    }

    public function testIsNotUnchanged(): void
    {
        /** @var ApplicationCompletion $appCompletion */
        $appCompletion = m::mock(ApplicationCompletion::class)->makePartial();
        $appCompletion->setVehiclesStatus(Application::VARIATION_STATUS_UNCHANGED);

        $sut = new SectionConfig();
        $sut->setVariationCompletion($appCompletion);

        $this->assertFalse($sut->isNotUnchanged('vehicles'));
    }

    public function testIsNotUnchangedWhenNotUnchanged(): void
    {
        /** @var ApplicationCompletion $appCompletion */
        $appCompletion = m::mock(ApplicationCompletion::class)->makePartial();
        $appCompletion->setVehiclesStatus(Application::VARIATION_STATUS_UPDATED);

        $sut = new SectionConfig();
        $sut->setVariationCompletion($appCompletion);

        $this->assertTrue($sut->isNotUnchanged('vehicles'));
    }
}
