<?php

/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Cli\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\View\Model\ConsoleModel;
use Common\Service\Entity\QueueEntityService;

/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

class BatchController extends AbstractConsoleController
{
    public function licenceStatusAction()
    {
        /* @var $batchService \Cli\Service\Processing\BatchLicenceStatusProcessingService */
        $batchService = $this->getServiceLocator()->get('BatchLicenceStatus');
        if ($this->isVerbose()) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        $batchService->processToRevokeCurtailSuspend();
        $batchService->processToValid();
    }

    public function inspectionRequestEmailAction()
    {
        /* @var $batchService \Cli\Service\Processing\BatchInspectionRequestEmailProcessingService */
        $batchService = $this->getServiceLocator()->get('BatchInspectionRequestEmail');
        if ($this->isVerbose()) {
            $batchService->setConsoleAdapter($this->getConsole());
        }

        $result = $batchService->process();

        return $this->handleExitStatus($result);
    }

    public function continuationNotSoughtAction()
    {
        $dryRun = $this->getRequest()->getParam('dryrun') || $this->getRequest()->getParam('d');

        /* @var $batchService \Cli\Service\Processing\ContinuationNotSought */
        $batchService = $this->getServiceLocator()->get('BatchContinuationNotSought');
        if ($this->isVerbose()) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        $batchService->process(['dryRun' => $dryRun]);

        // send the email
        if (!$dryRun) {
            $this->getServiceLocator()->get('Email\ContinuationNotSought')->send();
        }
    }

    public function processInboxDocumentsAction()
    {
        /* @var $batchService \Cli\Service\Processing\BatchInboxDocumentsProcessingService */
        $batchService = $this->getServiceLocator()->get('BatchInboxDocuments');
        if ($this->isVerbose()) {
            $batchService->setConsoleAdapter($this->getConsole());
        }

        $result = $batchService->process();

        return $this->handleExitStatus($result);
    }

    public function enqueueCompaniesHouseCompareAction()
    {
        // todo move to service and log returned count
        $restHelper = $this->getServiceLocator()->get('Helper\Rest');

        $result = $this->getServiceLocator()->get('Helper\Rest')->makeRestCall(
            'CompaniesHouseQueue',
            'POST',
            [
                'type' => QueueEntityService::TYPE_COMPANIES_HOUSE_COMPARE,
            ]
        );

        return $this->handleExitStatus(0);
    }

    private function isVerbose()
    {
        return $this->getRequest()->getParam('verbose') || $this->getRequest()->getParam('v');
    }

    /**
     * Using this method ensures the calling CLI environment gets an appropriate
     * exit code from the process.
     *
     * @param int $result exit code, should be non-zero if there was an error
     */
    private function handleExitStatus($result)
    {
        $model = new ConsoleModel();
        $model->setErrorLevel($result);
        return $model;
    }
}
