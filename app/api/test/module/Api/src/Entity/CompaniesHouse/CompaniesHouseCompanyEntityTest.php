<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\CompaniesHouse;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as Entity;

/**
 * CompaniesHouseCompany Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CompaniesHouseCompanyEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testArrayConversion(): void
    {
        $data = [
            'addressLine1' => 'test_addressLine1',
            'addressLine2' => 'test_addressLine2',
            'companyName' => 'test_companyName',
            'companyNumber' => 'test_companyNumber',
            'companyStatus' => 'test_companyStatus',
            'country' => 'test_country',
            'locality' => 'test_locality',
            'poBox' => 'test_poBox',
            'postalCode' => 'test_postalCode',
            'premises' => 'test_premises',
            'region' => 'test_region',
            'insolvencyProcessed' => 0,
            'officers' => [
                [
                    'name' => 'Bob',
                    'role' => 'Chief Ninja',
                    'dateOfBirth' => [
                        'year' => '1990',
                        'month' => '02',
                    ],
                ]
            ],
            'insolvencyPractitioners' => [
                [
                    'name' => 'Jim',
                    'addressLine1' => 'test_addressLine1',
                    'addressLine2' => 'test_addressLine2',
                    'country' => 'test_country',
                    'locality' => 'test_locality',
                    'postalCode' => 'test_postalCode',
                    'region' => 'test_region',
                    'appointedOn' => new \DateTime('1990-02-01 00:00:00')
                ]
            ]
        ];

        $sut = new Entity($data);

        $this->assertEquals('test_addressLine1', $sut->getAddressLine1());
        $this->assertEquals('test_addressLine2', $sut->getAddressLine2());
        $this->assertEquals('test_companyName', $sut->getCompanyName());
        $this->assertEquals('test_companyNumber', $sut->getCompanyNumber());
        $this->assertEquals('test_companyStatus', $sut->getCompanyStatus());
        $this->assertEquals('test_country', $sut->getCountry());
        $this->assertEquals('test_locality', $sut->getLocality());
        $this->assertEquals('test_poBox', $sut->getPoBox());
        $this->assertEquals('test_postalCode', $sut->getPostalCode());
        $this->assertEquals('test_premises', $sut->getPremises());
        $this->assertEquals('test_region', $sut->getRegion());
        $this->assertEquals(0, $sut->getInsolvencyProcessed());

        $expected = [
            'addressLine1' => 'test_addressLine1',
            'addressLine2' => 'test_addressLine2',
            'companyName' => 'test_companyName',
            'companyNumber' => 'test_companyNumber',
            'companyStatus' => 'test_companyStatus',
            'country' => 'test_country',
            'locality' => 'test_locality',
            'poBox' => 'test_poBox',
            'postalCode' => 'test_postalCode',
            'premises' => 'test_premises',
            'region' => 'test_region',
            'insolvencyProcessed' => 0,
            'officers' => [
                [
                    'name' => 'Bob',
                    'role' => 'Chief Ninja',
                    'dateOfBirth' => new \DateTime('1990-02-01 00:00:00'),
                ]
            ],
            'insolvencyPractitioners' => [
                [
                    'name' => 'Jim',
                    'addressLine1' => 'test_addressLine1',
                    'addressLine2' => 'test_addressLine2',
                    'country' => 'test_country',
                    'locality' => 'test_locality',
                    'postalCode' => 'test_postalCode',
                    'region' => 'test_region',
                    'appointedOn' => new \DateTime('1990-02-01 00:00:00')
                ]
            ]
        ];
        $this->assertEquals($expected, $sut->toArray());
    }
}
