<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\UpdateCompanySubsidiary;

/**
 * @covers Dvsa\Olcs\Transfer\Command\Licence\UpdateCompanySubsidiary
 */
final class UpdateCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = UpdateCompanySubsidiary::create(
            [
                'licence' => 7777,
            ]
        );

        $this->assertEquals(7777, $command->getLicence());
    }
}
