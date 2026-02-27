<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\Licence;

use Dvsa\Olcs\Api\Domain\Query\Licence\PsvLicenceSurrenderList;

/**
 * ContinuationNotSoughtList test
 */
class PsvLicenceSurrenderListTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $date = new \Datetime('2015-09-10');
        $query = PsvLicenceSurrenderList::create(['date' => $date]);

        $this->assertSame($date, $query->getDate());
    }
}
