<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusFeeTypeBundle;

/**
 * Class BusFeeTypeBundleTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusFeeTypeBundleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * test structure
     */
    public function testStructure(): void
    {
        $id = 1;
        $bundle = ['bundle'];

        $query = BusFeeTypeBundle::create(
            [
                'id' => $id,
                'bundle' => $bundle
            ]
        );

        $this->assertSame($id, $query->getId());
        $this->assertSame($bundle, $query->getBundle());
    }
}
