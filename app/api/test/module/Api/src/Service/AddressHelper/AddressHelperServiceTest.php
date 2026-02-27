<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\AddressHelper;

use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\DvsaAddressService\Service\AddressInterface;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class AddressHelperServiceTest extends TestCase
{
    private $addressServiceMock;
    private $postcodeEnforcementAreaRepositoryMock;
    private $adminAreaTrafficAreaRepositoryMock;
    private $addressHelperService;

    protected function setUp(): void
    {
        $this->addressServiceMock = $this->createMock(AddressInterface::class);
        $this->postcodeEnforcementAreaRepositoryMock = $this->createMock(Repository\PostcodeEnforcementArea::class);
        $this->adminAreaTrafficAreaRepositoryMock = $this->createMock(Repository\AdminAreaTrafficArea::class);

        $this->addressHelperService = new AddressHelperService(
            $this->addressServiceMock,
            $this->postcodeEnforcementAreaRepositoryMock,
            $this->adminAreaTrafficAreaRepositoryMock
        );
    }

    public function testLookupAddress(): void
    {
        $query = 'AB12 3CD';
        $expectedResult = []; // Assume this is populated with expected Address objects

        $this->addressServiceMock->method('lookupAddress')->willReturn($expectedResult);

        $result = $this->addressHelperService->lookupAddress($query);

        $this->assertEquals($expectedResult, $result);
    }

    public function testFetchTrafficAreaByPostcodeOrUprnValid(): void
    {
        $query = 'AB12 3CD';
        $expectedTrafficArea = $this->createMock(Entity\TrafficArea\TrafficArea::class);
        $expectedAdminAreaTrafficArea = $this->createMock(Entity\TrafficArea\AdminAreaTrafficArea::class);
        $expectedAdminAreaTrafficArea->method('getTrafficArea')->willReturn($expectedTrafficArea);
        $addressMock = $this->createMock(\Dvsa\Olcs\DvsaAddressService\Model\Address::class);
        $addressMock->method('getAdministrativeArea')->willReturn('AdminArea1');
        $this->addressServiceMock->method('lookupAddress')->willReturn([$addressMock]);
        $this->adminAreaTrafficAreaRepositoryMock->method('fetchById')->willReturn($expectedAdminAreaTrafficArea);

        $result = $this->addressHelperService->fetchTrafficAreaByPostcodeOrUprn($query);

        $this->assertEquals($expectedTrafficArea, $result);
    }

    public function testFetchTrafficAreaByPostcodeOrUprnInvalid(): void
    {
        $query = 'Invalid';
        $this->addressServiceMock->method('lookupAddress')->willReturn([]);

        $result = $this->addressHelperService->fetchTrafficAreaByPostcodeOrUprn($query);

        $this->assertNull($result);
    }

    public function testFetchEnforcementAreaByPostcodeValid(): void
    {
        $postcode = 'AB1 2CD';
        $expectedEnforcementArea = $this->createMock(Entity\EnforcementArea\EnforcementArea::class);
        $expectedPostcodeEnforcementArea = $this->createMock(Entity\EnforcementArea\PostcodeEnforcementArea::class);
        $expectedPostcodeEnforcementArea->expects($this->once())->method('getEnforcementArea')->willReturn($expectedEnforcementArea);
        $this->postcodeEnforcementAreaRepositoryMock->method('fetchByPostcodeId')->willReturn($expectedPostcodeEnforcementArea);

        $result = $this->addressHelperService->fetchEnforcementAreaByPostcode($postcode);

        $this->assertEquals($expectedEnforcementArea, $result);
    }

    public function testFetchEnforcementAreaByPostcodeInvalid(): void
    {
        $postcode = 'Invalid';
        $this->postcodeEnforcementAreaRepositoryMock->method('fetchByPostcodeId')->willReturn(null);

        $result = $this->addressHelperService->fetchEnforcementAreaByPostcode($postcode);

        $this->assertNull($result);
    }
}
