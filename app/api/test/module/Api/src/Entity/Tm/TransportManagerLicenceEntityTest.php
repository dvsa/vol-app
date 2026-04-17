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
class TransportManagerLicenceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  Entities\Licence\Licence | m\MockInterface */
    private $mockLic;
    /** @var  Entities\Tm\TransportManager */
    private $mockTm;

    /** @var  Entity */
    private $sut;

    public function setUp(): void
    {
        $this->mockLic  = m::mock(Entities\Licence\Licence::class);
        $this->mockTm  = m::mock(Entities\Tm\TransportManager::class);

        $this->sut = new Entity($this->mockLic, $this->mockTm);

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
        $this->assertEquals($this->sut->getTmType(), 'tmt');
        $this->assertEquals($this->sut->getHoursMon(), 1);
        $this->assertEquals($this->sut->getHoursTue(), 2);
        $this->assertEquals($this->sut->getHoursWed(), 3);
        $this->assertEquals($this->sut->getHoursThu(), 4);
        $this->assertEquals($this->sut->getHoursFri(), 5);
        $this->assertEquals($this->sut->getHoursSat(), 6);
        $this->assertEquals($this->sut->getHoursSun(), 7);
        $this->assertEquals($this->sut->getAdditionalInformation(), 'ai');
        $this->assertEquals($this->sut->getIsOwner(), 1);
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
            static::assertEquals(
                $e->getMessages(),
                [
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
                ]
            );
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
        $this->assertEquals($this->sut->getTotalWeeklyHours(), 28);
    }

    public function testGetRelatedOrganisation(): void
    {
        $mockOrg = m::mock(Entities\Organisation\Organisation::class);

        $this->mockLic->shouldReceive('getOrganisation')->once()->andReturn($mockOrg);

        static::assertEquals($mockOrg, $this->sut->getRelatedOrganisation());
    }
}
