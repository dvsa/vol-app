<?php

namespace Dvsa\OlcsTest\Api\Entity\Pi;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Pi\PiSlaException as Entity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Pi\SlaException as SlaExceptionEntity;
use Mockery as m;

/**
 * PiSlaException Entity Unit Tests
 *
 * @covers \Dvsa\Olcs\Api\Entity\Pi\PiSlaException
 */
class PiSlaExceptionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Test that the entity can be instantiated
     */
    public function testEntityInstantiation()
    {
        $entity = new Entity();
        $this->assertInstanceOf(Entity::class, $entity);
    }

    /**
     * Test PI relationship getter and setter
     */
    public function testPiGetterSetter()
    {
        $entity = new Entity();
        $pi = m::mock(PiEntity::class);
        
        $entity->setPi($pi);
        $this->assertSame($pi, $entity->getPi());
    }

    /**
     * Test SlaException relationship getter and setter
     */
    public function testSlaExceptionGetterSetter()
    {
        $entity = new Entity();
        $slaException = m::mock(SlaExceptionEntity::class);
        
        $entity->setSlaException($slaException);
        $this->assertSame($slaException, $entity->getSlaException());
    }

    /**
     * Test entity properties
     */
    public function testEntityProperties()
    {
        $entity = new Entity();
        
        // Test that all expected properties exist
        $this->assertObjectHasProperty('id', $entity);
        $this->assertObjectHasProperty('pi', $entity);
        $this->assertObjectHasProperty('slaException', $entity);
    }

    /**
     * Test ID setter and getter
     */
    public function testIdGetterSetter()
    {
        $entity = new Entity();
        $id = 123;
        
        $entity->setId($id);
        $this->assertEquals($id, $entity->getId());
    }

    /**
     * Test entity creation with relationships
     */
    public function testEntityWithRelationships()
    {
        $pi = m::mock(PiEntity::class);
        $slaException = m::mock(SlaExceptionEntity::class);
        
        $entity = new Entity();
        $entity->setPi($pi);
        $entity->setSlaException($slaException);
        
        $this->assertSame($pi, $entity->getPi());
        $this->assertSame($slaException, $entity->getSlaException());
    }

    /**
     * Test JSON serialization includes expected fields
     */
    public function testJsonSerialization()
    {
        $pi = m::mock(PiEntity::class);
        $pi->shouldReceive('getId')->andReturn(1);
        
        $slaException = m::mock(SlaExceptionEntity::class);
        $slaException->shouldReceive('getId')->andReturn(2);
        $slaException->shouldReceive('getDescription')->andReturn('Test Exception');
        
        $entity = new Entity();
        $entity->setId(1);
        $entity->setPi($pi);
        $entity->setSlaException($slaException);

        $jsonData = $entity->jsonSerialize();
        
        $this->assertIsArray($jsonData);
        $this->assertArrayHasKey('id', $jsonData);
    }

    /**
     * Test bundle serialization
     */
    public function testBundleSerialization()
    {
        $pi = m::mock(PiEntity::class);
        $pi->shouldReceive('serialize')->andReturn(['id' => 1]);
        
        $slaException = m::mock(SlaExceptionEntity::class);
        $slaException->shouldReceive('serialize')->andReturn(['id' => 2, 'description' => 'Test Exception']);
        
        $entity = new Entity();
        $entity->setId(1);
        $entity->setPi($pi);
        $entity->setSlaException($slaException);

        $bundle = [];
        $serialized = $entity->serialize($bundle);
        
        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('id', $serialized);
    }

    /**
     * Test that entity correctly handles null relationships
     */
    public function testNullRelationships()
    {
        $entity = new Entity();
        
        $this->assertNull($entity->getPi());
        $this->assertNull($entity->getSlaException());
    }

    /**
     * Test entity creation and validation
     */
    public function testEntityCreation()
    {
        $pi = m::mock(PiEntity::class);
        $slaException = m::mock(SlaExceptionEntity::class);
        
        $entity = new Entity();
        $entity->setId(1);
        $entity->setPi($pi);
        $entity->setSlaException($slaException);
        
        // Verify all properties are set correctly
        $this->assertEquals(1, $entity->getId());
        $this->assertInstanceOf(PiEntity::class, $entity->getPi());
        $this->assertInstanceOf(SlaExceptionEntity::class, $entity->getSlaException());
    }
}
