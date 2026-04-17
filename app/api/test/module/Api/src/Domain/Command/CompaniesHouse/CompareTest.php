<?php

declare(strict_types=1);

/**
 * Companies House Compare command test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\CompaniesHouse\Compare;

/**
 * Companies House Compare command test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompareTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = Compare::create(['companyNumber' => '01234567']);

        $this->assertEquals('01234567', $command->getCompanyNumber());
        $this->assertEquals(['companyNumber' => '01234567'], $command->getArrayCopy());
    }
}
