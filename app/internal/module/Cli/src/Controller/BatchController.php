<?php

/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Cli\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\View\Model\ConsoleModel;

/**
 * BatchController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

class BatchController extends AbstractConsoleController
{
    public function licenceStatusAction()
    {
        $verbose = $this->getRequest()->getParam('verbose') || $this->getRequest()->getParam('v');

        /* @var $batchService \Cli\Service\Processing\BatchLicenceStatusProcessingService */
        $batchService = $this->getServiceLocator()->get('BatchLicenceStatus');
        if ($verbose) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        $batchService->processToRevokeCurtailSuspend();
        $batchService->processToValid();
    }

    public function inspectionRequestEmailAction()
    {
        $verbose = $this->getRequest()->getParam('verbose') || $this->getRequest()->getParam('v');

        /* @var $batchService \Cli\Service\Processing\BatchInspectionRequestEmailProcessingService */
        $batchService = $this->getServiceLocator()->get('BatchInspectionRequestEmail');
        if ($verbose) {
            $batchService->setConsoleAdapter($this->getConsole());
        }

        $result = $batchService->process();

        $model = new ConsoleModel();
        $model->setErrorLevel($result);

        return $model;
    }

    public function continuationNotSoughtAction()
    {
        $verbose = $this->getRequest()->getParam('verbose') || $this->getRequest()->getParam('v');
        $dryRun = $this->getRequest()->getParam('dryrun') || $this->getRequest()->getParam('d');

        /* @var $batchService \Cli\Service\Processing\ContinuationNotSought */
        $batchService = $this->getServiceLocator()->get('BatchContinuationNotSought');
        if ($verbose) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        $batchService->process(['dryRun' => $dryRun]);

        // send the email
        if (!$dryRun) {
            $this->getServiceLocator()->get('Email\ContinuationNotSought')->send();
        }
    }

    public function companiesHousePollAction()
    {
        $verbose = $this->getRequest()->getParam('verbose') || $this->getRequest()->getParam('v');

        /* @var $batchService \Olcs\Service\Processing\BatchInspectionRequestEmailProcessingService */
        $batchService = $this->getServiceLocator()->get('BatchCompaniesHousePoll');
        if ($verbose) {
            $batchService->setConsoleAdapter($this->getConsole());
        }
        $batchService->process();
    }
}
