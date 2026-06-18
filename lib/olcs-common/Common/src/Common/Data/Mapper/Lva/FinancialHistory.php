<?php

/**
 * Financial History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Data\Mapper\Lva;

/**
 * Financial History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FinancialHistory
{
    /**
     * Map from API data to form data
     *
     * @param array $data API data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $data['financialHistoryConfirmation']['insolvencyConfirmation'] = $data['insolvencyConfirmation'];
        return [
            'data' => $data
        ];
    }
}
