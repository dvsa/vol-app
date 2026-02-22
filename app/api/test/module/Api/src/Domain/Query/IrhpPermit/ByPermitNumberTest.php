<?php

declare(strict_types=1);

/**
 * ByPermitNumber test
 */

namespace Dvsa\OlcsTest\Api\Domain\Query\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Query\IrhpPermit\ByPermitNumber;

class ByPermitNumberTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {

        $permitNum = 1001;
        $irhpPermitRange = 2;

        $query = ByPermitNumber::create(
            [
                'permitNumber' => $permitNum,
                'irhpPermitRange' => $irhpPermitRange,
            ]
        );

        $this->assertSame($permitNum, $query->getPermitNumber());
        $this->assertSame($irhpPermitRange, $query->getIrhpPermitRange());
        $this->assertSame([
            'permitNumber' => $permitNum,
            'irhpPermitRange' => $irhpPermitRange,
        ], $query->getArrayCopy());
    }
}
