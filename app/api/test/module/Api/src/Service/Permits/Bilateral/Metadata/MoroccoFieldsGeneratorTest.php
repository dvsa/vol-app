<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata\MoroccoFieldsGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * MoroccoFieldsGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MoroccoFieldsGeneratorTest extends MockeryTestCase
{
    public const PERIOD_NAME_KEY = 'period.name.key';

    private $irhpPermitStock;

    private $moroccoFieldsGenerator;

    public function setUp(): void
    {
        $this->irhpPermitStock = m::mock(IrhpPermitStock::class);
        $this->irhpPermitStock->shouldReceive('getPeriodNameKey')
            ->withNoArgs()
            ->andReturn(self::PERIOD_NAME_KEY);

        $this->moroccoFieldsGenerator = new MoroccoFieldsGenerator();
    }

    public function testGenerate(): void
    {
        $moroccoPermitsRequired = 17;

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($this->irhpPermitStock);

        $bilateralRequired = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $moroccoPermitsRequired
        ];

        $irhpPermitApplication->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);

        $expected = [
            'name' => 'permitsRequired',
            'caption' => self::PERIOD_NAME_KEY,
            'value' => $moroccoPermitsRequired,
        ];

        $this->assertEquals(
            $expected,
            $this->moroccoFieldsGenerator->generate($this->irhpPermitStock, $irhpPermitApplication)
        );
    }

    public function testGenerateNoIrhpPermitApplication(): void
    {
        $expected = [
            'name' => 'permitsRequired',
            'caption' => self::PERIOD_NAME_KEY,
            'value' => null,
        ];

        $this->assertEquals(
            $expected,
            $this->moroccoFieldsGenerator->generate($this->irhpPermitStock, null)
        );
    }

    public function testGenerateNonMatchingIrhpPermitApplication(): void
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn(m::mock(IrhpPermitStock::class));

        $expected = [
            'name' => 'permitsRequired',
            'caption' => self::PERIOD_NAME_KEY,
            'value' => null,
        ];

        $this->assertEquals(
            $expected,
            $this->moroccoFieldsGenerator->generate($this->irhpPermitStock, $irhpPermitApplication)
        );
    }
}
