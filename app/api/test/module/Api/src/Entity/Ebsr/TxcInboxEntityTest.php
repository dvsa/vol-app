<?php

namespace Dvsa\OlcsTest\Api\Entity\Ebsr;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox as Entity;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Mockery as m;

/**
 * TxcInbox Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TxcInboxEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests create
     *
     * @dataProvider validDataProvider
     *
     * @param $localAuthority
     * @param $organisation
     */
    public function testCreate($localAuthority, $organisation)
    {
        $variationNo = 999;

        $busReg = new BusRegEntity();
        $busReg->setIsTxcApp('Y');
        $busReg->setVariationNo($variationNo);
        $document = m::mock(DocumentEntity::class);

        $entity = new Entity($busReg, $document, $localAuthority, $organisation);

        $this->assertEquals($busReg, $entity->getBusReg());
        $this->assertEquals($variationNo, $entity->getVariationNo());
        $this->assertEquals($document, $entity->getZipDocument());
        $this->assertEquals($localAuthority, $entity->getLocalAuthority());
        $this->assertEquals($organisation, $entity->getOrganisation());
    }

    /**
     * Provides invalid data which should cause a validation error
     *
     * @return array
     */
    public function validDataProvider()
    {
        return [
            [new LocalAuthorityEntity(), null],
            [null, new OrganisationEntity()]
        ];
    }

    public function testCreateNotFromEbsr()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $busReg = new BusRegEntity();
        $busReg->setIsTxcApp('N');
        $document = m::mock(DocumentEntity::class);
        $localAuthority = new LocalAuthorityEntity();

        $entity = new Entity($busReg, $document, $localAuthority);
    }

    /**
     * @dataProvider createValidationErrorProvider
     *
     * @param $localAuthority
     * @param $organisation
     */
    public function testCreateValidationError($localAuthority, $organisation)
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $busReg = new BusRegEntity();
        $busReg->setIsTxcApp('Y');
        $document = m::mock(DocumentEntity::class);

        $entity = new Entity($busReg, $document, $localAuthority, $organisation);
    }

    /**
     * Provides invalid data which should cause a validation error
     *
     * @return array
     */
    public function createValidationErrorProvider()
    {
        return [
            [new LocalAuthorityEntity(), new OrganisationEntity()],
            [null, null]
        ];
    }

    public function testGetRelatedOrganisation()
    {
        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('getRelatedOrganisation')->with()->once()->andReturn('ORG 1');
        $busReg->shouldReceive('isFromEbsr')->with()->once()->andReturn(true);
        $busReg->shouldReceive('getVariationNo')->with()->once()->andReturn(1);
        $document = m::mock(DocumentEntity::class);
        $localAuthority = new LocalAuthorityEntity();

        $sut = new Entity($busReg, $document, $localAuthority);

        $this->assertSame('ORG 1', $sut->getRelatedOrganisation());
    }
}
