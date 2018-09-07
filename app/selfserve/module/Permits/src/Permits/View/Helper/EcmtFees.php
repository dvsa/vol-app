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

    /**
     * @param array $application
     * @return array
     */
    public function __invoke($application)
    {
        $data['totalFee'] = $application['irhpPermitApplications'][0]['successfulPermitApplications'] * $application['fees'][0]['grossAmount'];
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
        $dueDate = date(\DATE_FORMAT,strtotime("+10 days", strtotime($date)));
        return $dueDate;
    }
}
