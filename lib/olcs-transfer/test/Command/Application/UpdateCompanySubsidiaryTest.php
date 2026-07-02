<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateCompanySubsidiary;

/**
 * @covers Dvsa\Olcs\Transfer\Command\Application\UpdateCompanySubsidiary
 */
class UpdateCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = UpdateCompanySubsidiary::create(
            [
                'application' => 7777,
            ]
        );

        static::assertEquals(7777, $command->getApplication());
    }
}
