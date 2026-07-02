<?php

namespace Dvsa\OlcsTest\Transfer\Query\Correspondence;

use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences
 */
class CorrespondencesTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = Correspondences::create(
            [
                'organisation' => 'unit_OrgId',
                'page' => 'unit_Page',
                'limit' => 'unit_Limit',
            ]
        );

        static::assertEquals('unit_Page', $sut->getPage());
        static::assertEquals('unit_Limit', $sut->getLimit());
        static::assertEquals('unit_OrgId', $sut->getOrganisation());
    }
}
