<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Application\ApplicationTracking::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Application\AbstractApplicationTracking::class)]
final class ApplicationTrackingEntityTest extends EntityTester
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

        $actual = new Entity($mockApp)->jsonSerialize();
        $this->assertEquals(null, $actual['application']);
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
            'knowledgeExperienceStatus' => 11,
            'operatingCentresStatus' => 12,
            'peopleStatus' => 13,
            'safetyStatus' => 14,
            'taxiPhvStatus' => 15,
            'transportManagersStatus' => 16,
            'typeOfLicenceStatus' => 17,
            'declarationsInternalStatus' => 18,
            'vehiclesDeclarationsStatus' => 19,
            'vehiclesPsvStatus' => 20,
            'vehiclesStatus' => 21,
            'vehiclesSizeStatus' => 22,
            'psvOperateSmallStatus' => 23,
            'psvOperateLargeStatus' => 24,
            'psvSmallConditionsStatus' => 25,
            'psvOperateNoveltyStatus' => 26,
            'psvSmallPartWrittenStatus' => 27,
            'psvDocumentaryEvidenceSmallStatus' => 28,
            'psvDocumentaryEvidenceLargeStatus' => 29,
            'psvMainOccupationUndertakingsStatus' => 30,
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
        $this->assertEquals(11, $sut->getKnowledgeExperienceStatus());
        $this->assertEquals(12, $sut->getOperatingCentresStatus());
        $this->assertEquals(13, $sut->getPeopleStatus());
        $this->assertEquals(14, $sut->getSafetyStatus());
        $this->assertEquals(15, $sut->getTaxiPhvStatus());
        $this->assertEquals(16, $sut->getTransportManagersStatus());
        $this->assertEquals(17, $sut->getTypeOfLicenceStatus());
        $this->assertEquals(18, $sut->getDeclarationsInternalStatus());
        $this->assertEquals(19, $sut->getVehiclesDeclarationsStatus());
        $this->assertEquals(20, $sut->getVehiclesPsvStatus());
        $this->assertEquals(21, $sut->getVehiclesStatus());
        $this->assertEquals(22, $sut->getVehiclesSizeStatus());
        $this->assertEquals(23, $sut->getPsvOperateSmallStatus());
        $this->assertEquals(24, $sut->getPsvOperateLargeStatus());
        $this->assertEquals(25, $sut->getPsvSmallConditionsStatus());
        $this->assertEquals(26, $sut->getPsvOperateNoveltyStatus());
        $this->assertEquals(27, $sut->getPsvSmallPartWrittenStatus());
        $this->assertEquals(28, $sut->getPsvDocumentaryEvidenceSmallStatus());
        $this->assertEquals(29, $sut->getPsvDocumentaryEvidenceLargeStatus());
        $this->assertEquals(30, $sut->getPsvMainOccupationUndertakingsStatus());
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
