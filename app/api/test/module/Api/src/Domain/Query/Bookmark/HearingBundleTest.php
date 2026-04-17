<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\HearingBundle;

/**
 * Class HearingBundleTest
 */
class HearingBundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * test structure
     */
    public function testStructure(): void
    {
        $caseId = 1;
        $bundle = ['bundle'];

        $query = HearingBundle::create(
            [
                'case' => $caseId,
                'bundle' => $bundle
            ]
        );

        $this->assertSame($caseId, $query->getCase());
        $this->assertSame($bundle, $query->getBundle());
    }
}
