<?php

namespace Permits\Data\Mapper;

/**
 * @todo clearly this will need to be a lot better later - but will wait to see first if it's staying
 *
 * Fee list mapper
 */
class FeeList
{
    const FEE_STATUS_OUTSTANDING = 'lfs_ot';
    const FEE_TYPE_ISSUE = 'IRHPGVISSUE';

    public static function mapForDisplay (array $data) {
        foreach ($data['fees'] as $fee) {
            if ($fee['feeStatus']['id'] == self::FEE_STATUS_OUTSTANDING
                    && $fee['feeType']['feeType']['id'] == self::FEE_TYPE_ISSUE) {
                //return first occurence as there should only be one
                $data['issueFee'] = $fee['feeType']['displayValue'];
                $data['totalFee'] = $fee['grossAmount'];
                $data['dueDate'] = FeeList::calculateDueDate($fee['invoicedDate']);

                return $data;
            }
        }

        return null;
    }

    /**
     * add 10 days to the date
     * @param string $date
     * @return string
     */
    private function calculateDueDate($date)
    {
        $dueDate = date(\DATE_FORMAT, strtotime("+10 days", strtotime($date)));
        return $dueDate;
    }
}
