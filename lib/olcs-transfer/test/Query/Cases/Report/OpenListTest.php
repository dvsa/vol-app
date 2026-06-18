<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Cases\Report;

use Dvsa\Olcs\Transfer\Query\Cases\Report\OpenList;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see OpenList
 */
class OpenListTest extends MockeryTestCase
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

        static::assertEquals(['unit_Ta'], $sut->getTrafficAreas());
        static::assertEquals('unit_CaseType', $sut->getCaseType());
        static::assertEquals('unit_Lic', $sut->getLicenceStatus());
        static::assertEquals('unit_App', $sut->getApplicationStatus());
        static::assertCount(6, $sut->getArrayCopy());
    }
}
