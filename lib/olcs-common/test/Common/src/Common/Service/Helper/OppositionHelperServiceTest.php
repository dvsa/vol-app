<?php

/**
 * Opposition Helper Service Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\OppositionHelperService;

/**
 * Opposition Helper Service Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OppositionHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    public $helper;
    /**
     * Setup the helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->helper = new OppositionHelperService();
    }

    /**
     * test sortCasesOpenClosed
     */
    public function testSortCasesOpenClosed(): void
    {
        $oppositions = [
            ['case' => ['closedDate' => null]],
            ['case' => ['closedDate' => '2015-03-27']],
            ['case' => ['closedDate' => null]],
            ['case' => ['closedDate' => '2015-03-25']],
        ];

        $result = $this->helper->sortOpenClosed($oppositions);
        $this->assertEquals(
            [$oppositions[0], $oppositions[2], $oppositions[1], $oppositions[3]],
            $result
        );
    }
}
