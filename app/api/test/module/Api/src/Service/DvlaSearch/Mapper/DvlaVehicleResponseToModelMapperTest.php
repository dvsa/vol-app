<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\DvlaSearch\Mapper;

use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\BadResponseException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Mapper\DvlaVehicleResponseToModelMapper;
use PHPUnit\Framework\TestCase;

class DvlaVehicleResponseToModelMapperTest extends TestCase
{
    /**
     * @var DvlaVehicleResponseToModelMapper
     */
    protected $sut;

    public function setup(): void
    {
        $this->sut = new DvlaVehicleResponseToModelMapper();
    }

    public function testVehicleNodeMissing(): void
    {
        $this->expectException(BadResponseException::class);
        $this->sut->map([]);
    }

    public function testGetFromArray(): void
    {
        $result = $this->sut->map([
            'vehicle' => [
                "registrationNumber" => "WN67DSO",
                "taxStatus" => "Untaxed",
                "motStatus" => "No details held by DVLA",
                "make" => "ROVER",
                "yearOfManufacture" => 2004,
                "engineCapacity" => 1796,
                "co2Emissions" => 0,
                "fuelType" => "PETROL",
                "markedForExport" => true,
                "colour" => "Blue",
                "typeApproval" => "N1",
                "wheelplan" => "NON STANDARD",
                "revenueWeight" => 1640,
                "realDrivingEmissions" => "1",
                "euroStatus" => "Euro 5"
            ]
        ]);

        $this->assertSame(
            [
                "registrationNumber" => "WN67DSO",
                "taxStatus" => "Untaxed",
                'taxDueDate' => null,
                'artEndDate' => null,
                "motStatus" => "No details held by DVLA",
                'motExpiryDate' => null,
                "make" => "ROVER",
                'monthOfFirstDvlaRegistration' => null,
                'monthOfFirstRegistration' => null,
                "yearOfManufacture" => 2004,
                "engineCapacity" => 1796,
                "co2Emissions" => 0,
                "fuelType" => "PETROL",
                "markedForExport" => true,
                "colour" => "Blue",
                "typeApproval" => "N1",
                "wheelplan" => "NON STANDARD",
                "revenueWeight" => 1640,
                "realDrivingEmissions" => "1",
                'dateOfLastV5CIssued' => null,
                "euroStatus" => "Euro 5"
            ],
            $result->toArray()
        );
    }

    public function testDateParsing(): void
    {
        $result = $this->sut->map([
            'vehicle' => [
                "registrationNumber" => "WN67DSO",
                "taxDueDate" => "2017-12-25",
                'artEndDate' => 'abcdefg',
                "monthOfFirstDvlaRegistration" => "2011-11",
            ]
        ]);

        $this->assertInstanceOf(\DateTime::class, $result->getTaxDueDate());
        $this->assertSame('2017', $result->getTaxDueDate()->format('Y'));
        $this->assertSame('12', $result->getTaxDueDate()->format('m'));
        $this->assertSame('25', $result->getTaxDueDate()->format('d'));

        $this->assertNull($result->getArtEndDate());

        $this->assertInstanceOf(\DateTime::class, $result->getMonthOfFirstDvlaRegistration());
        $this->assertSame('2011', $result->getMonthOfFirstDvlaRegistration()->format('Y'));
        $this->assertSame('11', $result->getMonthOfFirstDvlaRegistration()->format('m'));
    }
}
