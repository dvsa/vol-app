<?php

declare(strict_types=1);

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
     *
     * @param $localAuthority
     * @param $organisation
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('validDataProvider')]
    public function testCreate(mixed $localAuthority, mixed $organisation): void
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
    public static function validDataProvider(): array
    {
        return [
            [new LocalAuthorityEntity(), null],
            [null, new OrganisationEntity()]
        ];
    }

    public function testCreateNotFromEbsr(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $busReg = new BusRegEntity();
        $busReg->setIsTxcApp('N');
        $document = m::mock(DocumentEntity::class);
        $localAuthority = new LocalAuthorityEntity();

        $entity = new Entity($busReg, $document, $localAuthority);
    }

    /**
     *
     * @param $localAuthority
     * @param $organisation
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('createValidationErrorProvider')]
    public function testCreateValidationError(mixed $localAuthority, mixed $organisation): void
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
    public static function createValidationErrorProvider(): array
    {
        return [
            [new LocalAuthorityEntity(), new OrganisationEntity()],
            [null, null]
        ];
    }

    public function testGetRelatedOrganisation(): void
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
