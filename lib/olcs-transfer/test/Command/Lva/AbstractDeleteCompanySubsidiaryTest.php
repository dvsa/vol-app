<?php

namespace Dvsa\OlcsTest\Transfer\Command\Lva;

use Dvsa\Olcs\Transfer\Command\Lva\AbstractDeleteCompanySubsidiary;

/**
 * @covers Dvsa\Olcs\Transfer\Command\Lva\AbstractDeleteCompanySubsidiary
 */
class AbstractDeleteCompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
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

        static::assertEquals([111, 222, 333], $command->getIds());
    }
}
