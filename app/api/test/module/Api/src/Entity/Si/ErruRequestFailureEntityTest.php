<?php

namespace Dvsa\OlcsTest\Api\Entity\Si;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Si\ErruRequestFailure as Entity;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Mockery as m;

/**
 * ErruRequestFailure Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ErruRequestFailureEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests creation of erru request failures
     */
    public function testCreate()
    {
        $document = m::mock(Document::class);

        $errors = ['foo' => 'bar'];
        $errorsJson = json_encode($errors);

        $input = ['bar' => 'foo'];
        $inputJson = json_encode($input);

        $entity = new Entity($document, $errors, $input);

        $this->assertEquals($document, $entity->getDocument());
        $this->assertEquals($errorsJson, $entity->getErrors());
        $this->assertEquals($inputJson, $entity->getInput());
    }

    /**
     * Makes sure if the input field is a string then the data is ignored
     */
    public function testCreateWithStringInput()
    {
        $document = m::mock(Document::class);


        $errors = ['foo' => 'bar'];
        $errorsJson = json_encode($errors);

        $input = 'some string';

        $entity = new Entity($document, $errors, $input);

        $this->assertEquals($document, $entity->getDocument());
        $this->assertEquals($errorsJson, $entity->getErrors());
        $this->assertNull($entity->getInput());
    }
}
