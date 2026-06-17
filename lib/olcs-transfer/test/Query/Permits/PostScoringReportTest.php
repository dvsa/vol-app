<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\PostScoringReport;

/**
 * Post scoring report test
 */
class PostScoringReportTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = PostScoringReport::create(['id' => 67]);

        $this->assertEquals(67, $query->getId());
    }
}
