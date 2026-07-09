<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Lva;

use Dvsa\Olcs\Transfer\Command\Lva\AbstractUpdateCompanySubsidiary;

/**
 * @covers Dvsa\Olcs\Transfer\Command\Lva\AbstractCreateCompanySubsidiary
 * @covers Dvsa\Olcs\Transfer\Command\Lva\AbstractUpdateCompanySubsidiary
 */
final class AbstractUpdateCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet()
    {
        $class = new class extends AbstractUpdateCompanySubsidiary {
        };

        $command = $class::create(
            [
                'id' => 111,
                'version' => 2,
                'name' => 'unit_name',
                'companyNo' => 'unit_CompNo',
            ]
        );

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(2, $command->getVersion());
        $this->assertEquals('unit_name', $command->getName());
        $this->assertEquals('unit_CompNo', $command->getCompanyNo());
    }
}
