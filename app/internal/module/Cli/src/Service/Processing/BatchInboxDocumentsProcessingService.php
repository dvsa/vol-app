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
use Common\Service\Helper\UrlHelperService;
use Common\Service\Helper\DocumentDispatchHelperService;
use Common\Service\File\File;

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
        $ciEntityService = $this->getServiceLocator()->get('Entity\CorrespondenceInbox');
        $minDate         = $this->getDate('P1M');
        $maxPrintDate    = $this->getDate('P1W');
        $maxReminderDate = $this->getDate('P2D');

        // @NOTE: The way we query both sets before updating either means there is a
        // potential overlap; if this process hasn't been running for a while we might
        // well get the same record to print AND email. But that's okay; the upshot is
        // the user gets an email and a letter.
        $printList = $ciEntityService->getAllRequiringPrint($minDate, $maxPrintDate);
        $emailList = $ciEntityService->getAllRequiringReminder($minDate, $maxReminderDate);

        $this->outputLine('Found ' . count($printList) . ' records to print');
        $this->outputLine('Found ' . count($emailList) . ' records to email');

        $updates = [];

        foreach ($emailList as $row) {
            $licence = $row['licence'];
            $organisation = $licence['organisation'];

            $isContinuation = !empty($row['document']['continuationDetails']);

            $users = $this->getServiceLocator()
                ->get('Entity\Organisation')
                ->getAdminEmailAddresses($organisation['id']);

            // edge case; we expect to find email addresses otherwise we wouldn't
            // have created the CI record in the first place, but still something
            // we need to handle...
            if (empty($users)) {
                $this->log('No admin email addresses for licence ' . $licence['id']);
                continue;
            }

            $url = $this->getServiceLocator()->get('Helper\Url')->fromRouteWithHost(
                UrlHelperService::EXTERNAL_HOST,
                'correspondence_inbox'
            );

            $this->outputLine('Sending email reminder for licence ' . $licence['id'] . ' to ' . implode($users, ','));

            $emailType = $isContinuation ?
                DocumentDispatchHelperService::TYPE_CONTINUATION :
                DocumentDispatchHelperService::TYPE_STANDARD;

            $this->getServiceLocator()
                ->get('Email')
                ->sendTemplate(
                    // never translate to welsh
                    false,
                    // default from name is fine
                    null,
                    // default from address is fine
                    null,
                    $users,
                    'email.inbox-reminder.' . $emailType . '.subject',
                    'markup-email-inbox-reminder-' . $emailType,
                    [$licence['licNo'], $url]
                );

            $updates[] = [
                'id' => $row['id'],
                'emailReminderSent' => 'Y',
                '_OPTIONS_' => ['force' => true]
            ];
        }

        foreach ($printList as $row) {
            $licence = $row['licence'];
            $document = $row['document'];

            // the enqueueFile interface expects a file, so let's
            // keep it happy...
            $file = new File();
            $file->fromData($document);

            $this->outputLine('Printing document for licence ' . $licence['id']);

            $this->getServiceLocator()
                ->get('PrintScheduler')
                ->enqueueFile($file, $document['description']);

            $updates[] = [
                'id' => $row['id'],
                'printed' => 'Y',
                '_OPTIONS_' => ['force' => true]
            ];
        }

        if (count($updates)) {
            $this->outputLine('Updating ' . count($updates). ' records');
            $ciEntityService->multiUpdate($updates);
        } else {
            $this->outputLine('No records to update');
        }

        return self::EXIT_CODE_SUCCESS;
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
