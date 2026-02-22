<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\System\DiscSequence as Entity;

/**
 * DiscSequence Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class DiscSequenceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetDiscPrefix(): void
    {
        $entity = new Entity();
        $entity->setRPrefix('AB');
        $entity->setSnPrefix('CD');
        $entity->setSiPrefix('EF');
        $this->assertEquals('AB', $entity->getDiscPrefix('ltyp_r'));
        $this->assertEquals('CD', $entity->getDiscPrefix('ltyp_sn'));
        $this->assertEquals('EF', $entity->getDiscPrefix('ltyp_si'));
    }

    public function testSetAndGetDiscNumber(): void
    {
        $entity = new Entity();
        $entity->setDiscStartNumber('ltyp_r', 1);
        $entity->setDiscStartNumber('ltyp_sn', 2);
        $entity->setDiscStartNumber('ltyp_si', 3);
        $this->assertEquals(1, $entity->getDiscNumber('ltyp_r'));
        $this->assertEquals(2, $entity->getDiscNumber('ltyp_sn'));
        $this->assertEquals(3, $entity->getDiscNumber('ltyp_si'));
    }
}
