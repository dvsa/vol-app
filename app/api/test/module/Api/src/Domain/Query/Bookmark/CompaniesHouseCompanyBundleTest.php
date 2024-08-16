<?php

namespace Dvsa\OlcsTest\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\CompaniesHouseCompanyBundle;
use PHPUnit\Framework\TestCase;

class CompaniesHouseCompanyBundleTest extends TestCase
{
    public function testStructure()
    {
        $licenceId = 12;
        $bundle = [];

        $query = CompaniesHouseCompanyBundle::create(
            [
                'id' => $licenceId,
                'bundle' => $bundle
            ]
        );

        $this->assertEquals($licenceId, $query->getId());
        $this->assertEquals($bundle, $query->getBundle());
    }
}
