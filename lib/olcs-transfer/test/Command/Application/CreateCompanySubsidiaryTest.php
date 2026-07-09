<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\CreateCompanySubsidiary;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Application\CreateCompanySubsidiary::class)]
final class CreateCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = CreateCompanySubsidiary::create(
            [
                'application' => 7777,
            ]
        );

        $this->assertEquals(7777, $command->getApplication());
    }
}
