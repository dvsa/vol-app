<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Pi;

use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as Entity;
use Mockery as m;

/**
 * PresidingTc Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PresidingTcEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreateUpdate(): void
    {
        $user = m::mock(User::class)->makePartial();
        $name = 'Test TC';

        $entity = Entity::create($name, $user);

        $this->assertEquals($name, $entity->getName());
        $this->assertEquals($user, $entity->getUser());

        $entity->update($name, $user);

        $this->assertEquals($name, $entity->getName());
        $this->assertEquals($user, $entity->getUser());
    }
}
