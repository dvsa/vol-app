<?php

/**
 * Opposition helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Common\Service\Helper;

/**
 * Opposition helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OppositionHelperService
{
    /**
     * Sort oppositions so that open are at the top, this will keep any existing order eg createdDate
     *
     * @param array $oppositions
     *
     * @return array
     */
    public function sortOpenClosed($oppositions)
    {
        // sort the results so that open cases are first but still in date order
        $openOppositions = [];
        $closedOppositions = [];
        foreach ($oppositions as $row) {
            if ($row['case']['closedDate']) {
                $closedOppositions[] = $row;
            } else {
                $openOppositions[] = $row;
            }
        }

        return array_merge($openOppositions, $closedOppositions);
    }
}
