<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\UniqueCountriesByLicence;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpPermit\UniqueCountriesByLicence
 */
class UniqueCountriesByLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = UniqueCountriesByLicence::create(
            [
                'licence' => 7,
                'irhpPermitType' => 2,
            ]
        );
        static::assertEquals(7, $sut->getLicence());
        static::assertEquals(2, $sut->getIrhpPermitType());
        static::assertNull($sut->getValidOnly());
        static::assertEquals(
            [
                'licence' => 7,
                'irhpPermitType' => 2,
                'validOnly' => null,
            ],
            $sut->getArrayCopy()
        );
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
        static::assertEquals(7, $sut->getLicence());
        static::assertEquals(2, $sut->getIrhpPermitType());
        static::assertEquals(true, $sut->getValidOnly());
        static::assertEquals(
            [
                'licence' => 7,
                'irhpPermitType' => 2,
                'validOnly' => true,
            ],
            $sut->getArrayCopy()
        );
    }
}
