<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Correspondence;

use Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Correspondence\Correspondences::class)]
final class CorrespondencesTest extends MockeryTestCase
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

        $this->assertEquals('unit_Page', $sut->getPage());
        $this->assertEquals('unit_Limit', $sut->getLimit());
        $this->assertEquals('unit_OrgId', $sut->getOrganisation());
    }
}
