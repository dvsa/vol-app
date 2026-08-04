<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\UpdateCompanySubsidiary;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Licence\UpdateCompanySubsidiary::class)]
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
