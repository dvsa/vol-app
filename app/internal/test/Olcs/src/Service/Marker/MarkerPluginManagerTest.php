<?php

namespace OlcsTest\Service\Marker;

use Olcs\Service\Marker\MarkerPluginManager;

/**
 * Class MarkerPluginManagerTest
 * @package OlcsTest\Service\Data
 */
class MarkerPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new MarkerPluginManager();
    }

    public function testValidatePlugin()
    {
        $plugin = new \StdClass();
        $this->assertTrue($this->sut->validatePlugin($plugin));
    }
}
