<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence
 */
class GetListByLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = GetListByLicence::create(
            [
                'licence' => 7,
                'page' => 1,
                'limit' => 10,
            ]
        );
        static::assertEquals(7, $sut->getLicence());
        static::assertEquals(1, $sut->getPage());
        static::assertEquals(10, $sut->getLimit());
        static::assertNull($sut->getIrhpPermitType());
        static::assertNull($sut->getCountry());
        static::assertNull($sut->getStatus());
        static::assertNull($sut->getValidOnly());
        static::assertEquals(
            [
                'licence' => 7,
                'page' => 1,
                'limit' => 10,
                'irhpPermitType' => null,
                'country' => null,
                'status' => null,
                'validOnly' => null,
            ],
            $sut->getArrayCopy()
        );
    }

    public function testStructureOptional()
    {
        $sut = GetListByLicence::create(
            [
                'licence' => 7,
                'irhpPermitType' => 2,
                'country' => 'DE',
                'page' => 1,
                'limit' => 10,
                'status' => 'permit_status',
                'validOnly' => true,
            ]
        );
        static::assertEquals(7, $sut->getLicence());
        static::assertEquals(1, $sut->getPage());
        static::assertEquals(10, $sut->getLimit());
        static::assertEquals(2, $sut->getIrhpPermitType());
        static::assertEquals('DE', $sut->getCountry());
        static::assertEquals('permit_status', $sut->getStatus());
        static::assertEquals(true, $sut->getValidOnly());
        static::assertEquals(
            [
                'licence' => 7,
                'irhpPermitType' => 2,
                'country' => 'DE',
                'page' => 1,
                'limit' => 10,
                'status' => 'permit_status',
                'validOnly' => true,
            ],
            $sut->getArrayCopy()
        );
    }
}
