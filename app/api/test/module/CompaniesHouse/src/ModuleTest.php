<?php

namespace Dvsa\OlcsTest\CompaniesHouse;

use Dvsa\Olcs\CompaniesHouse\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testGetConfig()
    {
        $sut = new Module();
        $config = $sut->getConfig();

        $this->assertIsArray($config);
    }
}
