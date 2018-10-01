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

    /**
     * @param array $application
     * @return array
     */
    public function __invoke($application)
    {
        $data['issueFee'] = $this->getOutstandingIssueFee($application);
        $data['totalFee'] = $application['irhpPermitApplications'][0]['permitsAwarded'] * $data['issueFee'];
        $data['dueDate'] = $this->calculateDueDate($application['fees'][0]['invoicedDate']);
        return $data;
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

    /**
     * get the outstanding issuing fee from an array of application data
     * @param array $application
     * @return string
     */
    private function getOutstandingIssueFee($application)
    {
        foreach ($application['fees'] as $fee) {
            if ($fee['feeStatus']['id'] == $this::FEE_STATUS_OUTSTANDING) {
                return $fee['grossAmount']; //return first occurence as there should only be one
            }
        }

        return null;
    }
}
