<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

/**
 * Calculate ECMT permit fees
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.co.uk>
 */
class EcmtFees extends AbstractHelper
{
     const FEE_STATUS_OUTSTANDING = 'lfs_ot';
     const FEE_TYPE_ISSUE = 'IRHPGVISSUE';

    /**
     * @param array $application
     * @return array
     */
    public function __invoke($application)
    {
        foreach ($application['fees'] as $fee) {
            if ($fee['feeStatus']['id'] == $this::FEE_STATUS_OUTSTANDING
                && $fee['feeType']['feeType']['id'] == $this::FEE_TYPE_ISSUE) {
                //return first occurence as there should only be one
                return [
                    'issueFee' => $fee['feeType']['displayValue'],
                    'totalFee' => $fee['grossAmount'],
                    'dueDate' => $this->calculateDueDate($fee['invoicedDate'])
                ];
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
