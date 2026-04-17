<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\CaseBundle;

class CaseBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
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
