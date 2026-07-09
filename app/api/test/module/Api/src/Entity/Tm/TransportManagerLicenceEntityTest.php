<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Tm;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence
 * @covers Dvsa\Olcs\Api\Entity\Tm\AbstractTransportManagerLicence
 */
final class TransportManagerLicenceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  Entities\Licence\Licence | m\MockInterface */
    private $mockLic;

    /** @var  Entity */
    private $sut;

    public function setUp(): void
    {
        $this->mockLic  = m::mock(Entities\Licence\Licence::class);
        $mockTm  = m::mock(Entities\Tm\TransportManager::class);

        $this->sut = new Entity($this->mockLic, $mockTm);

        parent::setUp();
    }

    public function testUpdateTransportManagerLicence(): void
    {
        $this->sut->updateTransportManagerLicence(
            'tmt',
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            'ai',
            1
        );
        $this->assertEquals('tmt', $this->sut->getTmType());
        $this->assertEquals(1, $this->sut->getHoursMon());
        $this->assertEquals(2, $this->sut->getHoursTue());
        $this->assertEquals(3, $this->sut->getHoursWed());
        $this->assertEquals(4, $this->sut->getHoursThu());
        $this->assertEquals(5, $this->sut->getHoursFri());
        $this->assertEquals(6, $this->sut->getHoursSat());
        $this->assertEquals(7, $this->sut->getHoursSun());
        $this->assertEquals('ai', $this->sut->getAdditionalInformation());
        $this->assertEquals(1, $this->sut->getIsOwner());
    }

    public function testUpdateTransportManagerLicenceInvalid(): void
    {
        try {
            $this->sut->updateTransportManagerLicence(
                'tmt',
                25,
                25,
                25,
                25,
                25,
                25,
                25,
                'ai',
                1
            );
        } catch (ValidationException $e) {
            $this->assertEquals($e->getMessages(), [
                [
                    'hoursMon' => [Entity::ERROR_MON => 'Mon must be between 0 and 24, inclusively'],
                ],
                [
                    'hoursTue' => [Entity::ERROR_TUE => 'Tue must be between 0 and 24, inclusively'],
                ],
                [
                    'hoursWed' => [Entity::ERROR_WED => 'Wed must be between 0 and 24, inclusively'],
                ],
                [
                    'hoursThu' => [Entity::ERROR_THU => 'Thu must be between 0 and 24, inclusively'],
                ],
                [
                    'hoursFri' => [Entity::ERROR_FRI => 'Fri must be between 0 and 24, inclusively'],
                ],
                [
                    'hoursSat' => [Entity::ERROR_SAT => 'Sat must be between 0 and 24, inclusively'],
                ],
                [
                    'hoursSun' => [Entity::ERROR_SUN => 'Sun must be between 0 and 24, inclusively']
                ],
            ]);
        }
    }

    public function testGetTotalWeeklyHours(): void
    {
        $this->sut->updateTransportManagerLicence(
            'tmt',
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            'ai',
            1
        );
        $this->assertEquals(28, $this->sut->getTotalWeeklyHours());
    }

    public function testGetRelatedOrganisation(): void
    {
        $mockOrg = m::mock(Entities\Organisation\Organisation::class);

        $this->mockLic->shouldReceive('getOrganisation')->once()->andReturn($mockOrg);

        $this->assertEquals($mockOrg, $this->sut->getRelatedOrganisation());
    }
}
