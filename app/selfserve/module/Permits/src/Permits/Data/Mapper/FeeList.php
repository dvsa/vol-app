<?php

namespace Permits\Data\Mapper;

use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;

/**
 * @todo clearly this will need to be a lot better later - but will wait to see first if it's staying
 *
 * Fee list mapper
 */
class FeeList
{
    const FEE_TYPE_ISSUE = 'IRHPGVISSUE';

    /**
     * Maps fee data to easy to reference indexes
     *
     * @param array $data an array of data retrieved from the backend
     * @return array the same array as passed in except possibly
     *                  with additional indexes for fee data
     */
    public static function mapForDisplay(array $data)
    {
        foreach ($data['fees'] as $fee) {
            if ($fee['feeStatus']['id'] == Fee::STATUS_OUTSTANDING
                    && $fee['feeType']['feeType']['id'] == FeeType::FEE_TYPE_ECMT_ISSUE) {
                //return first occurence as there should only be one
                $data['issueFee'] = $fee['feeType']['displayValue'];
                $data['totalFee'] = $fee['grossAmount'];
                $data['dueDate'] = self::calculateDueDate($fee['invoicedDate']);

                return $data;
            }
        }

        throw new \RuntimeException(
            'No outstanding issuing fees were found.'
        );
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
