<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * IrhpPermitRange Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpPermitRangeEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreateUpdate(): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $prefix = "UK";
        $fromNo = "1";
        $toNo = "150";
        $isReserve = 0;
        $isReplacement = 0;
        $countrys = [];
        $emissionsCategory = m::mock(RefData::class);
        $journey = m::mock(RefData::class);
        $cabotage = 0;

        $updatedPrefix = "AU";
        $updatedFromNo = "10";
        $updatedToNo = "1500";
        $updatedIsReserve = 1;
        $updatedCountrys = ['AU'];
        $updatedJourney = m::mock(RefData::class);
        $updatedCabotage = 1;

        $entity = Entity::create(
            $irhpPermitStock,
            $emissionsCategory,
            $prefix,
            $fromNo,
            $toNo,
            $isReserve,
            $isReplacement,
            $countrys,
            $journey,
            $cabotage
        );

        $this->assertEquals($irhpPermitStock, $entity->getIrhpPermitStock());
        $this->assertEquals($prefix, $entity->getPrefix());
        $this->assertEquals($fromNo, $entity->getFromNo());
        $this->assertEquals($toNo, $entity->getToNo());
        $this->assertEquals($isReserve, $entity->getSsReserve());
        $this->assertEquals($isReplacement, $entity->getLostReplacement());
        $this->assertEquals($countrys, $entity->getCountrys());
        $this->assertSame($emissionsCategory, $entity->getEmissionsCategory());
        $this->assertSame($journey, $entity->getJourney());
        $this->assertEquals($cabotage, $entity->getCabotage());

        $entity->update(
            $irhpPermitStock,
            $emissionsCategory,
            $updatedPrefix,
            $updatedFromNo,
            $updatedToNo,
            $updatedIsReserve,
            $isReplacement,
            $updatedCountrys,
            $updatedJourney,
            $updatedCabotage
        );

        $this->assertEquals($irhpPermitStock, $entity->getIrhpPermitStock());
        $this->assertEquals($updatedPrefix, $entity->getPrefix());
        $this->assertEquals($updatedFromNo, $entity->getFromNo());
        $this->assertEquals($updatedToNo, $entity->getToNo());
        $this->assertEquals($updatedIsReserve, $entity->getSsReserve());
        $this->assertEquals($isReplacement, $entity->getLostReplacement());
        $this->assertEquals($updatedCountrys, $entity->getCountrys());
        $this->assertSame($emissionsCategory, $entity->getEmissionsCategory());
        $this->assertSame($updatedJourney, $entity->getJourney());
        $this->assertEquals($updatedCabotage, $entity->getCabotage());
    }

    /**
     * Test the canDelete method
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testCanDelete(mixed $data, mixed $expected): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $prefix = "UK";
        $fromNo = "1";
        $toNo = "150";
        $isReserve = 0;
        $isReplacement = 0;
        $emissionsCategory = m::mock(RefData::class);
        $journey = m::mock(RefData::class);
        $cabotage = 0;

        $entity = Entity::create(
            $irhpPermitStock,
            $emissionsCategory,
            $prefix,
            $fromNo,
            $toNo,
            $isReserve,
            $isReplacement,
            $data['countrys'],
            $journey,
            $cabotage
        );
        $entity->setIrhpPermits($data['irhpPermits']);
        $entity->setIrhpCandidatePermits($data['irhpCandidatePermits']);
        $this->assertEquals($expected, $entity->canDelete());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function provider(): array
    {
        return [
            'valid delete' => [
                [
                    'irhpCandidatePermits' => [],
                    'countrys' => [],
                    'irhpPermits' => []
                ],
                true,
            ],
            'existing candidate permits' => [
                [
                    'irhpCandidatePermits' => [m::mock(IrhpCandidatePermit::class)],
                    'countrys' => [],
                    'irhpPermits' => []
                ],
                false
            ],
            'existing countries' => [
                [
                    'irhpCandidatePermits' => [],
                    'countrys' => [m::mock(Country::class)],
                    'irhpPermits' => []
                ],
                false
            ],
            'existing irhp permits' => [
                [
                    'irhpCandidatePermits' => [],
                    'countrys' => [],
                    'irhpPermits' => [m::mock(IrhpPermit::class)]
                ],
                false
            ],
            'candidate permits and countries' => [
                [
                    'irhpCandidatePermits' => [m::mock(IrhpCandidatePermit::class)],
                    'countrys' => [m::mock(Country::class)],
                    'irhpPermits' => []
                ],
                false
            ],
            'candidate permits and irhp permits' => [
                [
                    'irhpCandidatePermits' => [m::mock(IrhpCandidatePermit::class)],
                    'countrys' => [],
                    'irhpPermits' => [m::mock(IrhpPermit::class)]
                ],
                false
            ],
            'countries and irhp permits' => [
                [
                    'irhpCandidatePermits' => [],
                    'countrys' => [m::mock(Country::class)],
                    'irhpPermits' => [m::mock(IrhpPermit::class)]
                ],
                false
            ],
            'candidate permits, countries and irhp permits' => [
                [
                    'irhpCandidatePermits' => [m::mock(IrhpCandidatePermit::class)],
                    'countrys' => [m::mock(Country::class)],
                    'irhpPermits' => [m::mock(IrhpPermit::class)]
                ],
                false
            ],
        ];
    }

    public function testGetSize(): void
    {
        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $prefix = "UK";
        $fromNo = "75";
        $toNo = "150";
        $isReserve = 0;
        $isReplacement = 0;
        $countrys = [];
        $emissionsCategory = m::mock(RefData::class);
        $journey = m::mock(RefData::class);
        $cabotage = 0;

        $entity = Entity::create(
            $irhpPermitStock,
            $emissionsCategory,
            $prefix,
            $fromNo,
            $toNo,
            $isReserve,
            $isReplacement,
            $countrys,
            $journey,
            $cabotage
        );

        $this->assertEquals(76, $entity->getSize());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasCountries')]
    public function testHasCountries(array $countries, mixed $expectedHasCountries): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setCountrys(new ArrayCollection($countries));

        $this->assertEquals(
            $expectedHasCountries,
            $entity->hasCountries()
        );
    }

    public static function dpHasCountries(): array
    {
        return [
            [
                [],
                false
            ],
            [
                [m::mock(Country::class)],
                true
            ],
            [
                [m::mock(Country::class), m::mock(Country::class)],
                true
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsCabotage')]
    public function testIsCabotage(mixed $cabotage, mixed $expected): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setCabotage($cabotage);

        $this->assertEquals($expected, $entity->isCabotage());
    }

    public static function dpIsCabotage(): array
    {
        return [
            [false, false],
            [true, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsStandard')]
    public function testIsStandard(mixed $cabotage, mixed $expected): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setCabotage($cabotage);

        $this->assertEquals($expected, $entity->isStandard());
    }

    public static function dpIsStandard(): array
    {
        return [
            [false, true],
            [true, false],
        ];
    }
}
