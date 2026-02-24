<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\CompaniesHouse;

use Dvsa\Olcs\CompaniesHouse\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testGetConfig(): void
    {
        $sut = new Module();
        $config = $sut->getConfig();

        $this->assertIsArray($config);
    }
}
