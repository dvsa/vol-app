<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\CreateCompanySubsidiary;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Licence\CreateCompanySubsidiary::class)]
final class CreateCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $command = CreateCompanySubsidiary::create(
            [
                'licence' => 7777,
            ]
        );

        $this->assertEquals(7777, $command->getLicence());
    }
}
