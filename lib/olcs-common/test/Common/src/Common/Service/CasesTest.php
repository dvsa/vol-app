<?php

namespace CommonTest\Service;

use Common\Service\Cases;

/**
 * Class CasesTest
 * @package CommonTest\Service
 */
class CasesTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateNrCase(): array
    {
        $case = 29;

        $data = [
            'case' => $case
        ];

        $sut = new Cases();
        $result = $sut->createNrCase($data);

        $this->assertIsArray($result);
        $this->assertSame($case, $result['case']);

        $this->assertEquals(Cases::NR_CATEGORY_DEFAULT, $result['erruCaseType']);
        $this->assertEquals(Cases::CASE_CATEGORY_NR, $result['caseType']);
        $this->assertEquals(Cases::NR_DEFAULT_INFRINGEMENT_CATEGORY, $result['seriousInfringements'][0]['siCategory']);

        return $result;
    }
}
