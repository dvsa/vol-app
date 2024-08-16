<?php

namespace Dvsa\OlcsTest\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\CaseBundle;

class CaseBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $caseId = 1;
        $bundle = ['bundle'];

        $query = CaseBundle::create(
            [
                'id' => $caseId,
                'bundle' => $bundle
            ]
        );

        $this->assertSame($caseId, $query->getId());
        $this->assertSame($bundle, $query->getBundle());
    }
}
