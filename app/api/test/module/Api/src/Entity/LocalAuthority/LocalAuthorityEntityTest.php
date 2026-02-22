<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\LocalAuthority;

use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * Local Authority Entity Unit Tests
 */
class LocalAuthorityEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testUpdate(): void
    {
        $description = 'some lta name';
        $emailAddress = 'some@email.com';
        $entity = new Entity();
        $entity->update($description, $emailAddress);
        $this->assertEquals($description, $entity->getDescription());
        $this->assertEquals($emailAddress, $entity->getEmailAddress());
    }
}
