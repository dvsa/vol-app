<?php

/**
 * Complaints helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Common\Service\Helper;

use Common\RefData;

/**
 * Complaints helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ComplaintsHelperService
{
    /**
     * From cases, create a sorted array of complaints
     *
     * @param array $cases
     *
     * @return array
     */
    public function sortCasesOpenClosed($cases)
    {
        // sort the results so that open cases are first but still in date order
        $openComplaints = [];
        $closedComplaints = [];
        foreach ($cases as $row) {
            if ($row['status']['id'] === RefData::COMPLAIN_STATUS_CLOSED) {
                $closedComplaints[] = $row;
            } else {
                $openComplaints[] = $row;
            }
        }

        return array_merge($openComplaints, $closedComplaints);
    }
}
