<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre as Entity;
use Dvsa\Olcs\Api\Entity\Application\S4;

/**
 * LicenceOperatingCentre Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class LicenceOperatingCentreEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstructor(): void
    {
        $licence = $this->createStub(Licence::class);
        $operatingCentre = $this->createStub(OperatingCentre::class);
        $sut = new Entity($licence, $operatingCentre);
        $this->assertSame($licence, $sut->getLicence());
        $this->assertSame($operatingCentre, $sut->getOperatingCentre());
    }

    /**
     * Get/setup and S4
     *
     * @param string $outcomeId refdata id
     *
     * @return S4
     */
    protected function setupS4(mixed $outcomeId = null): mixed
    {
        $s4 = $this->instantiate(S4::class);
        if ($outcomeId !== null) {
            $outcome = new \Dvsa\Olcs\Api\Entity\System\RefData();
            $outcome->setId($outcomeId);
            $s4->setOutcome($outcome);
        }

        return $s4;
    }

    public function testCanDeleteNoS4(): void
    {
        $entity = $this->instantiate(Entity::class);

        $checkCanDelete = $entity->checkCanDelete();
        $this->assertSame([], $checkCanDelete);
    }

    public function testCanDeleteS4WithoutOutcome(): void
    {
        $entity = $this->instantiate(Entity::class);
        $entity->setS4($this->setupS4());
        $this->assertArrayHasKey('OC_CANNOT_DELETE_HAS_S4', $entity->checkCanDelete());
    }

    public function testCanDeleteS4OutcomeApproved(): void
    {
        $entity = $this->instantiate(Entity::class);
        $entity->setS4($this->setupS4(S4::STATUS_APPROVED));
        $this->assertArrayHasKey('OC_CANNOT_DELETE_HAS_S4', $entity->checkCanDelete());
    }

    public function testCanDeleteS4OutcomeRejected(): void
    {
        $entity = $this->instantiate(Entity::class);
        $entity->setS4($this->setupS4(S4::STATUS_REFUSED));

        $checkCanDelete = $entity->checkCanDelete();
        $this->assertSame([], $checkCanDelete);
    }

    public function testGetRelatedOrganisation(): void
    {
        $org = m::mock();

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getRelatedOrganisation')->andReturn($org);

        $entity = $this->instantiate(Entity::class);
        $entity->setLicence($licence);

        $this->assertSame($org, $entity->getRelatedOrganisation());
    }
}
