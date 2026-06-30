<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\CreateCompanySubsidiary;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\Application\CreateCompanySubsidiary
 */
class CreateCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = CreateCompanySubsidiary::create(
            [
                'application' => 7777,
            ]
        );

        static::assertEquals(7777, $command->getApplication());
    }
}
