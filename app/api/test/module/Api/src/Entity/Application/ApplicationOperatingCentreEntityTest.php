<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre as Entity;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre
 * @covers Dvsa\Olcs\Api\Entity\Application\AbstractApplicationOperatingCentre
 */
class ApplicationOperatingCentreEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

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

    public function testConstructor(): void
    {
        /** @var Application $mockApp */
        $mockApp = m::mock(Application::class);
        /** @var OperatingCentre $mockOc */
        $mockOc = m::mock(OperatingCentre::class);

        $sut = new Entity($mockApp, $mockOc);

        static::assertSame($mockApp, $sut->getApplication());
        static::assertSame($mockOc, $sut->getOperatingCentre());
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

        $application = m::mock(Application::class);
        $application->shouldReceive('getRelatedOrganisation')->andReturn($org);

        $entity = $this->instantiate(Entity::class);
        $entity->setApplication($application);

        $this->assertSame($org, $entity->getRelatedOrganisation());
    }
}
