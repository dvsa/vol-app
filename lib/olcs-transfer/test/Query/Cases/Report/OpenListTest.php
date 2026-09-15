<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Cases\Report;

use Dvsa\Olcs\Transfer\Query\Cases\Report\OpenList;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see OpenList
 */
final class OpenListTest extends MockeryTestCase
{
    public function testStructure()
    {
        $sut = OpenList::create(
            [
                'trafficAreas' => ['unit_Ta'],
                'caseType' => 'unit_CaseType',
                'applicationStatus' => 'unit_App',
                'licenceStatus' => 'unit_Lic',
            ]
        );

        $this->assertEquals(['unit_Ta'], $sut->getTrafficAreas());
        $this->assertEquals('unit_CaseType', $sut->getCaseType());
        $this->assertEquals('unit_Lic', $sut->getLicenceStatus());
        $this->assertEquals('unit_App', $sut->getApplicationStatus());
        $this->assertCount(6, $sut->getArrayCopy());
    }
}
