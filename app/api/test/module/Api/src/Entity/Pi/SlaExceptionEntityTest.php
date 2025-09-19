<?php

namespace Dvsa\OlcsTest\Api\Entity\Pi;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Pi\SlaException as Entity;
use Mockery as m;

/**
 * SlaException Entity Unit Tests
 *
 * @covers \Dvsa\Olcs\Api\Entity\Pi\SlaException
 */
class SlaExceptionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /** @var  \Dvsa\Olcs\Api\Entity\Pi\SlaException */
    protected $entity;

    public function setUp(): void
    {
        /** @var \Dvsa\Olcs\Api\Entity\Pi\SlaException entity */
        $this->entity = $this->instantiate($this->entityClass);
    }

    /**
     * Test entity creation with constructor
     */
    public function testCreate()
    {
        $slaDescription = 'Test SLA Description';
        $slaExceptionDescription = 'Test Exception Description';
        $effectiveFrom = new \DateTime('2024-01-01');

        $sut = new Entity($slaDescription, $slaExceptionDescription, $effectiveFrom);

        $this->assertEquals($slaDescription, $sut->getSlaDescription());
        $this->assertEquals($slaExceptionDescription, $sut->getSlaExceptionDescription());
        $this->assertEquals($effectiveFrom, $sut->getEffectiveFrom());
        $this->assertNull($sut->getEffectiveTo()); // Should be null initially
    }

    /**
     * Test isActive method with various date scenarios
     */
    public function testIsActiveMethod()
    {
        $effectiveFrom = new \DateTime('2024-01-01');
        $entity = new Entity('Test SLA', 'Test Exception', $effectiveFrom);
        
        // Test active when current date is after effective from
        $checkDate = new \DateTime('2024-06-01');
        $this->assertTrue($entity->isActive($checkDate));
        
        // Test inactive when current date is before effective from
        $checkDate = new \DateTime('2023-12-01');
        $this->assertFalse($entity->isActive($checkDate));
        
        // Test with effective to date
        $entity->setEffectiveTo(new \DateTime('2024-12-31'));
        
        // Should be active between dates
        $checkDate = new \DateTime('2024-06-01');
        $this->assertTrue($entity->isActive($checkDate));
        
        // Should be inactive after effective to date
        $checkDate = new \DateTime('2025-01-01');
        $this->assertFalse($entity->isActive($checkDate));
    }

    /**
     * Test __toString method
     */
    public function testToString()
    {
        $entity = new Entity('Test SLA Description', 'Test Exception Description', new \DateTime());
        $expected = 'Test SLA Description - Test Exception Description';
        $this->assertEquals($expected, (string) $entity);
    }
}
