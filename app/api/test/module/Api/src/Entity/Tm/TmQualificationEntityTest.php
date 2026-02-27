<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Tm;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification as Entity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Mockery as m;

/**
 * TmQualification Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TmQualificationEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreate(): void
    {
        $tm = m::mock(TransportManager::class);
        $country = m::mock(Country::class);
        $qualificationType = m::mock(RefData::class);
        $serialNo = 'serial number';

        $entity = Entity::create($tm, $country, $qualificationType, $serialNo);

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertSame($tm, $entity->getTransportManager());
        $this->assertSame($country, $entity->getCountryCode());
        $this->assertSame($qualificationType, $entity->getQualificationType());
        $this->assertSame($serialNo, $entity->getSerialNo());
    }

    public function testUpdateTmQualification(): void
    {
        $entity = new Entity();

        $entity->updateTmQualification(
            'qtype',
            '123',
            '2015-01-01',
            'GB',
            1
        );

        $this->assertEquals('qtype', $entity->getQualificationType());
        $this->assertEquals('123', $entity->getSerialNo());
        $this->assertEquals(new \DateTime('2015-01-01'), $entity->getIssuedDate());
        $this->assertEquals('GB', $entity->getCountryCode());
        $this->assertEquals(1, $entity->getTransportManager());
    }

    public function testUpdateTmQualificationWithException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $entity = new Entity();

        $entity->updateTmQualification(
            'qtype',
            '123',
            '2222-01-01',
            'GB'
        );
    }
}
