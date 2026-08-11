<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence::class)]
final class GetListByLicenceTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals(7, $sut->getLicence());
        $this->assertEquals(1, $sut->getPage());
        $this->assertEquals(10, $sut->getLimit());
        $this->assertNull($sut->getIrhpPermitType());
        $this->assertNull($sut->getCountry());
        $this->assertNull($sut->getStatus());
        $this->assertNull($sut->getValidOnly());
        $this->assertEquals([
            'licence' => 7,
            'page' => 1,
            'limit' => 10,
            'irhpPermitType' => null,
            'country' => null,
            'status' => null,
            'validOnly' => null,
        ], $sut->getArrayCopy());
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
        $this->assertEquals(7, $sut->getLicence());
        $this->assertEquals(1, $sut->getPage());
        $this->assertEquals(10, $sut->getLimit());
        $this->assertEquals(2, $sut->getIrhpPermitType());
        $this->assertEquals('DE', $sut->getCountry());
        $this->assertEquals('permit_status', $sut->getStatus());
        $this->assertEquals(true, $sut->getValidOnly());
        $this->assertEquals([
            'licence' => 7,
            'irhpPermitType' => 2,
            'country' => 'DE',
            'page' => 1,
            'limit' => 10,
            'status' => 'permit_status',
            'validOnly' => true,
        ], $sut->getArrayCopy());
    }
}
