<?php

/**
 * Complaints Helper Service Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\ComplaintsHelperService;

/**
 * Complaints Helper Service Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ComplaintsHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    public $helper;
    /**
     * Setup the helper
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->helper = new ComplaintsHelperService();
    }

    /**
     * test sortCasesOpenClosed
     */
    public function testSortCasesOpenClosed(): void
    {
        $cases = [
            [
                'complaintDate' => 'complaintDate',
                'complainantContactDetails' => 'complainantContactDetails',
                'description' => 'description',
                'status' => ['id' => 'ecst_closed'],
            ],
            [
                'complaintDate' => 'complaintDate',
                'complainantContactDetails' => 'complainantContactDetails',
                'description' => 'description',
                'status' => ['id' => 'ecst_closed'],
            ],
            [
                'complaintDate' => 'complaintDate',
                'complainantContactDetails' => 'complainantContactDetails',
                'description' => 'description',
                'status' => ['id' => 'ecst_open'],
            ],
        ];
        $expected = [
            $cases[2],
            $cases[0],
            $cases[1],
        ];

        $result = $this->helper->sortCasesOpenClosed($cases);
        $this->assertEquals($expected, $result);
    }
}
