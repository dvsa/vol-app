<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Tm;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication
 * @covers Dvsa\Olcs\Api\Entity\Tm\AbstractTransportManagerApplication
 */
final class TransportManagerApplicationEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  Entity */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new Entity();

        parent::setUp();
    }

    public function testUpdateOperatorDigitalSignature(): void
    {
        $signatureType = m::mock(RefData::class);
        $signature = m::mock(DigitalSignature::class);

        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateOperatorDigitalSignature($signatureType, $signature);
        $this->assertEquals($signatureType, $sut->getOpSignatureType());
        $this->assertEquals($signature, $sut->getOpDigitalSignature());
    }

    public function testUpdateTmDigitalSignature(): void
    {
        $signatureType = m::mock(RefData::class);
        $signature = m::mock(DigitalSignature::class);

        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateTmDigitalSignature($signatureType, $signature);
        $this->assertEquals($signatureType, $sut->getTmSignatureType());
        $this->assertEquals($signature, $sut->getTmDigitalSignature());
    }

    public function testUpdateTransportManagerApplication(): void
    {
        $this->sut->updateTransportManagerApplication(1, 2, 'A', 'st');
        $this->assertEquals(1, $this->sut->getApplication());
        $this->assertEquals(2, $this->sut->getTransportManager());
        $this->assertEquals('A', $this->sut->getAction());
        $this->assertEquals('st', $this->sut->getTmApplicationStatus());
    }

    public function testUpdateTransportManagerApplicationFull(): void
    {
        $this->sut->updateTransportManagerApplicationFull(
            'tmt',
            1,
            'Y',
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            'ai',
            'tmas'
        );
        $this->assertEquals('tmt', $this->sut->getTmType());
        $this->assertEquals(1, $this->sut->getIsOwner());
        $this->assertEquals('Y', $this->sut->getHasUndertakenTraining());
        $this->assertEquals(1, $this->sut->getHoursMon());
        $this->assertEquals(2, $this->sut->getHoursTue());
        $this->assertEquals(3, $this->sut->getHoursWed());
        $this->assertEquals(4, $this->sut->getHoursThu());
        $this->assertEquals(5, $this->sut->getHoursFri());
        $this->assertEquals(6, $this->sut->getHoursSat());
        $this->assertEquals(7, $this->sut->getHoursSun());
        $this->assertEquals('ai', $this->sut->getAdditionalInformation());
        $this->assertEquals('tmas', $this->sut->getTmApplicationStatus());
    }

    public function testUpdateTransportManagerApplicationFullInvalid(): void
    {
        try {
            $this->sut->updateTransportManagerApplicationFull(
                'tmt',
                1,
                'N',
                25,
                25,
                25,
                25,
                25,
                25,
                25,
                'ai',
                'tmas'
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
        $this->sut->updateTransportManagerApplicationFull(
            'tmt',
            1,
            'Y',
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            'ai',
            'tmas'
        );
        $this->assertEquals(28, $this->sut->getTotalWeeklyHours());
    }
}
