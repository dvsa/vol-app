<?php

namespace SelfServe\test\Tables\Table;

class OperatingCentreTest extends \PHPUnit_Framework_TestCase
{

    public function testConfigTablePsvDependency()
    {
        $config = include('./module/SelfServe/src/SelfServe/Table/Tables/operatingcentre.table.php');
        $this->assertArrayHasKey('trailersCol', $config['columns']);
        $this->assertArrayHasKey('format', $config['footer'][0]);
        $this->assertArrayHasKey(2, $config['footer']);
    }

}