<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\Lva;

use Dvsa\Olcs\Transfer\Command\Lva\AbstractDeleteCompanySubsidiary;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\Lva\AbstractDeleteCompanySubsidiary::class)]
final class AbstractDeleteCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet()
    {
        $class = new class extends AbstractDeleteCompanySubsidiary {
        };

        $command = $class::create(
            [
                'ids' => [111, 222, 333],
            ]
        );

        $this->assertEquals([111, 222, 333], $command->getIds());
    }
}
