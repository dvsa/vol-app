<?php

namespace Dvsa\OlcsTest\Transfer\Query\Organisation;

use Dvsa\Olcs\Transfer\Query\Application\FinancialEvidence;

/**
 * Financial Evidence Test
 */
class FinancialEvidenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = FinancialEvidence::create(['id' => 111, 'foo' => 'bar']);

        $this->assertEquals(111, $query->getId());
    }
}
