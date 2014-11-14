<?php

namespace OlcsTest\Service\Data;

use Mockery as m;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Class CloseButtonTraitTest
 * @package OlcsTest\Controller\Traits
 */
class CloseButtonTraitTest extends \PHPUnit_Framework_TestCase
{
    public $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Service\Data\Submission();
    }

    /**
     * Tests getting the close button
     */
    public function testGetClose()
    {
        $id = 99;
        $this->sut->setEntityName('foo');
        $result = $this->sut->getCloseButton($id);

        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('route', $result);
        $this->assertArrayHasKey('case', $result['params']);
        $this->assertArrayHasKey('action', $result['params']);
        $this->assertArrayHasKey('params', $result);
        $this->assertEquals('foo', $result['route']);
        $this->assertArrayHasKey('foo', $result['params']);
    }

    /**
     * Tests getting the reopen button
     */
    public function testReopenClose()
    {
        $id = 99;
        $this->sut->setEntityName('foo');
        $result = $this->sut->getReopenButton($id);

        $this->assertArrayHasKey('label', $result);
        $this->assertContains('Reopen', $result['label']);
        $this->assertArrayHasKey('route', $result);
        $this->assertArrayHasKey('case', $result['params']);
        $this->assertArrayHasKey('action', $result['params']);
        $this->assertArrayHasKey('params', $result);
        $this->assertEquals('foo', $result['route']);
        $this->assertArrayHasKey('foo', $result['params']);
    }

    /**
     * Tests getEntityName
     */
    public function testGetEntityName()
    {
        $entityName = 'foo';
        $this->sut->setEntityName('foo');

        $this->assertEquals($entityName, $this->sut->getEntityName());

        $this->sut->setEntityName('');
        $this->assertEquals('submission', $this->sut->getEntityName());

    }
}
