<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\PostScoringReport;

/**
 * Post scoring report test
 */
final class PostScoringReportTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = PostScoringReport::create(['id' => 67]);

        $this->assertEquals(67, $query->getId());
    }
}
