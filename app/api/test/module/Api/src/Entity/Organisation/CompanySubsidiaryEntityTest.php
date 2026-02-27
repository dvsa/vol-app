<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Organisation;

use Mockery as m;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary as Entity;

/**
 * CompanySubsidiary Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CompanySubsidiaryEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetCalculatedValues(): void
    {
        $licence = m::mock(Licence::class);

        $entity = new Entity('Foo', '123456789', $licence);
        $data = $entity->jsonSerialize();

        $this->assertSame($licence, $entity->getLicence());
        $this->assertEquals('Foo', $data['name']);
        $this->assertEquals('123456789', $data['companyNo']);
        $this->assertNull($data['licence']);
    }

    public function testGetRelatedOrganisation(): void
    {
        $org = m::mock();

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getOrganisation')->andReturn($org);

        $entity = new Entity('Foo', '123456789', $licence);

        $this->assertSame($org, $entity->getRelatedOrganisation());
    }
}
