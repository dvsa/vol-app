<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Mockery as m;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\Workshop as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Workshop Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class WorkshopEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $licence = m::mock(Licence::class);
        $contactDetails = m::mock(ContactDetails::class);

        $entity = new Entity($licence, $contactDetails);

        $this->assertSame($licence, $entity->getLicence());
        $this->assertSame($contactDetails, $entity->getContactDetails());

        $this->assertNull($entity->jsonSerialize()['licence']);
    }
}
