<?php

declare(strict_types=1);

/**
 * ByPermitNumber test
 */

namespace Dvsa\OlcsTest\Api\Domain\Query\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\Query\IrhpPermitRange\ByPermitNumber;

class ByPermitNumberTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $permitStock = 1001;
        $permitNumber = 2;

        $query = ByPermitNumber::create(
            [
                'permitStock' => $permitStock,
                'permitNumber' => $permitNumber,
            ]
        );

        $this->assertSame($permitStock, $query->getPermitStock());
        $this->assertSame($permitNumber, $query->getPermitNumber());
        $this->assertSame(
            [
                'permitStock' => $permitStock,
                'permitNumber' => $permitNumber,
            ],
            $query->getArrayCopy()
        );
    }
}
