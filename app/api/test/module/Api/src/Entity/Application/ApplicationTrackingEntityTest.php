<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Application\ApplicationTracking
 * @covers Dvsa\Olcs\Api\Entity\Application\AbstractApplicationTracking
 */
class ApplicationTrackingEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $application = m::mock(Application::class);

        $at = new Entity($application);

        $this->assertSame($application, $at->getApplication());
    }

    public function testGetCalculatedValues(): void
    {
        /** @var Application $mockApp */
        $mockApp = m::mock(Application::class);

        $actual = (new Entity($mockApp))->jsonSerialize();
        static::assertEquals(null, $actual['application']);
    }

    public function testGetValueOptions(): void
    {
        $this->assertEquals(
            [
                0 => '',
                1 => 'Accepted',
                2 => 'Not accepted',
                3 => 'Not applicable',
            ],
            Entity::getValueOptions()
        );
    }

    public function testExchangeStatusArray(): void
    {
        /** @var Entity|m\MockInterface $sut */
        $sut = m::mock(Entity::class)->makePartial();

        $data = [
            'addressesStatus' => 1,
            'businessDetailsStatus' => 2,
            'businessTypeStatus' => 3,
            'communityLicencesStatus' => 4,
            'conditionsUndertakingsStatus' => 5,
            'convictionsPenaltiesStatus' => 6,
            'discsStatus' => 7,
            'financialEvidenceStatus' => 8,
            'financialHistoryStatus' => 9,
            'licenceHistoryStatus' => 10,
            'operatingCentresStatus' => 11,
            'peopleStatus' => 12,
            'safetyStatus' => 13,
            'taxiPhvStatus' => 14,
            'transportManagersStatus' => 15,
            'typeOfLicenceStatus' => 16,
            'declarationsInternalStatus' => 17,
            'vehiclesDeclarationsStatus' => 18,
            'vehiclesPsvStatus' => 19,
            'vehiclesStatus' => 20,
            'vehiclesSizeStatus' => 21,
            'psvOperateSmallStatus' => 22,
            'psvOperateLargeStatus' => 23,
            'psvSmallConditionsStatus' => 24,
            'psvOperateNoveltyStatus' => 25,
            'psvSmallPartWrittenStatus' => 26,
            'psvDocumentaryEvidenceSmallStatus' => 27,
            'psvDocumentaryEvidenceLargeStatus' => 28,
            'psvMainOccupationUndertakingsStatus' => 29,
        ];

        $sut->exchangeStatusArray($data);

        $this->assertEquals(1, $sut->getAddressesStatus());
        $this->assertEquals(2, $sut->getBusinessDetailsStatus());
        $this->assertEquals(3, $sut->getBusinessTypeStatus());
        $this->assertEquals(4, $sut->getCommunityLicencesStatus());
        $this->assertEquals(5, $sut->getConditionsUndertakingsStatus());
        $this->assertEquals(6, $sut->getConvictionsPenaltiesStatus());
        $this->assertEquals(7, $sut->getDiscsStatus());
        $this->assertEquals(8, $sut->getFinancialEvidenceStatus());
        $this->assertEquals(9, $sut->getFinancialHistoryStatus());
        $this->assertEquals(10, $sut->getLicenceHistoryStatus());
        $this->assertEquals(11, $sut->getOperatingCentresStatus());
        $this->assertEquals(12, $sut->getPeopleStatus());
        $this->assertEquals(13, $sut->getSafetyStatus());
        $this->assertEquals(14, $sut->getTaxiPhvStatus());
        $this->assertEquals(15, $sut->getTransportManagersStatus());
        $this->assertEquals(16, $sut->getTypeOfLicenceStatus());
        $this->assertEquals(17, $sut->getDeclarationsInternalStatus());
        $this->assertEquals(18, $sut->getVehiclesDeclarationsStatus());
        $this->assertEquals(19, $sut->getVehiclesPsvStatus());
        $this->assertEquals(20, $sut->getVehiclesStatus());
        $this->assertEquals(21, $sut->getVehiclesSizeStatus());
        $this->assertEquals(22, $sut->getPsvOperateSmallStatus());
        $this->assertEquals(23, $sut->getPsvOperateLargeStatus());
        $this->assertEquals(24, $sut->getPsvSmallConditionsStatus());
        $this->assertEquals(25, $sut->getPsvOperateNoveltyStatus());
        $this->assertEquals(26, $sut->getPsvSmallPartWrittenStatus());
        $this->assertEquals(27, $sut->getPsvDocumentaryEvidenceSmallStatus());
        $this->assertEquals(28, $sut->getPsvDocumentaryEvidenceLargeStatus());
        $this->assertEquals(29, $sut->getPsvMainOccupationUndertakingsStatus());
    }

    public function testIsValidEmpty(): void
    {
        $sections = [];

        /** @var Entity $at */
        $at = $this->instantiate(Entity::class);

        $this->assertTrue($at->isValid($sections));
    }

    public function testIsValid(): void
    {
        $sections = [
            'businessType'
        ];

        /** @var Entity $at */
        $at = $this->instantiate(Entity::class);
        $at->setBusinessTypeStatus(Entity::STATUS_NOT_ACCEPTED);

        $this->assertFalse($at->isValid($sections));
    }

    public function testIsValidWhenValid(): void
    {
        $sections = [
            'businessType'
        ];

        /** @var Entity $at */
        $at = $this->instantiate(Entity::class);
        $at->setBusinessTypeStatus(Entity::STATUS_ACCEPTED);

        $this->assertTrue($at->isValid($sections));
    }
}
