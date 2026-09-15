<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Organisation;

use Dvsa\Olcs\Transfer\Query\Application\FinancialEvidence;

/**
 * Financial Evidence Test
 */
final class FinancialEvidenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = FinancialEvidence::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $query->getId());
    }
}
