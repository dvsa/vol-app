<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\UniqueCountriesByLicence;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpPermit\UniqueCountriesByLicence::class)]
final class UniqueCountriesByLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = UniqueCountriesByLicence::create(
            [
                'licence' => 7,
                'irhpPermitType' => 2,
            ]
        );
        $this->assertEquals(7, $sut->getLicence());
        $this->assertEquals(2, $sut->getIrhpPermitType());
        $this->assertNull($sut->getValidOnly());
        $this->assertEquals([
            'licence' => 7,
            'irhpPermitType' => 2,
            'validOnly' => null,
        ], $sut->getArrayCopy());
    }

    public function testStructureOptional()
    {
        $sut = UniqueCountriesByLicence::create(
            [
                'licence' => 7,
                'irhpPermitType' => 2,
                'validOnly' => true,
            ]
        );
        $this->assertEquals(7, $sut->getLicence());
        $this->assertEquals(2, $sut->getIrhpPermitType());
        $this->assertEquals(true, $sut->getValidOnly());
        $this->assertEquals([
            'licence' => 7,
            'irhpPermitType' => 2,
            'validOnly' => true,
        ], $sut->getArrayCopy());
    }
}
