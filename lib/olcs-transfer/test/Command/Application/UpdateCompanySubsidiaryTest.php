<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateCompanySubsidiary;

/**
 * @covers Dvsa\Olcs\Transfer\Command\Application\UpdateCompanySubsidiary
 */
final class UpdateCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = UpdateCompanySubsidiary::create(
            [
                'application' => 7777,
            ]
        );

        $this->assertEquals(7777, $command->getApplication());
    }
}
