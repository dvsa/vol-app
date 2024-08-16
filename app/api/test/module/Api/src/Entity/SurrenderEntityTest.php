<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity;

use Dvsa\Olcs\Api\Entity\DigitalSignature;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Surrender as Entity;
use Mockery as m;

/**
 * Surrender Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class SurrenderEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testUpdateDigitalSignature(): void
    {
        $signatureType = m::mock(RefData::class);
        $signature = m::mock(DigitalSignature::class);
        $surrenderStatus = m::mock(RefData::class);
        $licenceStatus = m::mock(RefData::class);

        $licence = m::mock(Licence::class)->makePartial();

        $sut = m::mock(Entity::class)->makePartial();
        $sut->setLicence($licence);

        $sut->updateDigitalSignature($surrenderStatus, $licenceStatus, $signatureType, $signature);
        $this->assertEquals($signatureType, $sut->getSignatureType());
        $this->assertEquals($signature, $sut->getDigitalSignature());
        $this->assertEquals($surrenderStatus, $sut->getStatus());
        $this->assertEquals($licenceStatus, $sut->getLicence()->getStatus());
    }

    public function testGetContextValue(): void
    {
        $entity = new Entity();
        $entity->setId(190);

        $this->assertEquals(190, $entity->getContextValue());
    }
}
