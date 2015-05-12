<?php

/**
 * Batch process inbox documents and send reminder emails
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Cli\Service\Processing;

use Common\Service\Entity\LicenceStatusRuleEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\BusinessService\Response;

/**
 * Batch process inbox documents and send reminder emails
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BatchInboxDocumentsProcessingService extends AbstractBatchProcessingService
{
    const EXIT_CODE_ERROR = 1;
    const EXIT_CODE_SUCCESS = 0;

    /**
     * Process emails
     *
     * @return void
     */
    public function process()
    {
        $minDate         = $this->getDate('P1M');
        $maxPrintDate    = $this->getDate('P1W');
        $maxReminderDate = $this->getDate('P2D');

        $printList = $this->getServiceLocator()
            ->get('Entity\CorrespondenceInbox')
            ->getAllRequiringPrint($minDate, $maxPrintDate);

        // print each item in printList
        // update each item in print list to flag it as printed

        $emailList = $this->getServiceLocator()
            ->get('Entity\CorrespondenceInbox')
            ->getAllRequiringReminder($minDate, $maxReminderDate);

        // email each item in emailList
        // ^^ will require explicitly telling the email service about
        // our custom template
        // update each item in email list to flag it as emailed
    }

    private function getDate($interval)
    {
        return $this->getServiceLocator()
            ->get('Helper\Date')
            ->getDateObject()
            ->sub(new \DateInterval($interval))
            ->format(\DateTime::W3C);
    }
}
